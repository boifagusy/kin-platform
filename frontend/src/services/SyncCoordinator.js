/**
 * SyncCoordinator — Single owner of queue lifecycle and synchronization.
 * Singleton (FR-09). FR-08: Exactly one active instance for the shared queue key.
 *
 * S3: Telemetry & Metrics — observational only, never blocks sync (FR-09).
 * FR-10: No backend changes. Metrics are client-side and in-memory only.
 */

import { LocationQueue } from './LocationQueue.js';
import { SyncQueue } from './SyncQueue.js';
import networkDetection from './NetworkDetection.js';

const API_BASE = import.meta.env.VITE_API_URL || '/api/v1';

let instance = null;

class SyncCoordinator {
  constructor() {
    if (instance) return instance;

    this.queue = new LocationQueue(null, 'sync_coordinator_queue');
    this.syncQueue = null;
    this._cleanup = null;
    this._started = false;

    // S3: Metrics store — in-memory only, O(1) updates
    this._metrics = {
      current: {
        queue_depth: 0,
        peak_queue_depth: 0,
        peak_queue_depth_at: null,
        oldest_item_age_ms: 0,
        started: false,
      },
      cumulative: {
        total_enqueued: 0,
        total_synced: 0,
        total_retries: 0,
        total_dead_letter: 0,
        total_drains: 0,
      },
      timing: {
        last_success_at: null,
        last_failure_at: null,
        last_failure_reason: null,
        sync_started_at: null,
        sync_stopped_at: null,
        last_drain_duration_ms: 0,
        total_drain_duration_ms: 0,
      },
    };

    instance = this;
  }

  // --- Lifecycle ---

  start() {
    if (this._started) return;

    this._setGauge('current', 'started', true);
    this._recordTiming('sync_started_at');

    this.syncQueue = new SyncQueue(this.queue, async (item) => {
      let endpoint = `${API_BASE}/sync`;
      if (item.type === 'sos') endpoint = `${API_BASE}/sos`;
      else if (item.type === 'checkin') endpoint = `${API_BASE}/checkin`;

      const token = localStorage.getItem('kin_token');
      const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      };
      if (token) headers['Authorization'] = `Bearer ${token}`;
      if (item.operation_id) headers['X-Operation-Id'] = item.operation_id;

      const response = await fetch(endpoint, {
        method: 'POST',
        headers,
        body: JSON.stringify(item),
      });

      if (!response.ok) {
        throw new Error(`Upload failed: ${response.status}`);
      }
      return true;
    });

    // Override drain to capture metrics
    const originalDrain = this.syncQueue.drain.bind(this.syncQueue);
    this.syncQueue.drain = async (limit = 10, delayMs = 0) => {
      this._increment('cumulative', 'total_drains');
      const startTime = performance.now();
      const result = await originalDrain(limit, delayMs);
      const duration = performance.now() - startTime;

      this._setGauge('timing', 'last_drain_duration_ms', Math.round(duration));
      this._setGauge('timing', 'total_drain_duration_ms',
        (this._metrics.timing.total_drain_duration_ms || 0) + Math.round(duration));

      // Sync drain results into metrics
      this._addToCounter('cumulative', 'total_synced', result.synced || 0);
      this._addToCounter('cumulative', 'total_dead_letter', result.dead || 0);

      if (result.synced > 0) this._recordTiming('last_success_at');
      if (result.dead > 0) {
        this._recordTiming('last_failure_at');
        this._setGauge('timing', 'last_failure_reason', 'Retry exhausted');
      }

      return result;
    };

    this.syncQueue.syncOnStart(10, 3000);
    this._cleanup = this.syncQueue.syncOnReconnect(10, 2000);

    if (networkDetection.isOnline()) {
      setTimeout(() => {
        this.syncQueue.drain(10, 500).catch(err => {
          console.warn('SyncCoordinator: initial drain failed:', err);
        });
      }, 1000);
    }

    this._started = true;
  }

  stop() {
    if (this._cleanup) {
      this._cleanup();
      this._cleanup = null;
    }
    this._setGauge('current', 'started', false);
    this._recordTiming('sync_stopped_at');
    this._started = false;
  }

  // --- Public API ---

  async enqueue(type, payload) {
    const item = {
      type,
      payload,
      timestamp: Date.now(),
      operation_id: crypto.randomUUID(),
    };
    await this.queue.enqueue(item);

    // S3: Update metrics (FR-09: never throw)
    try {
      this._increment('cumulative', 'total_enqueued');
      const size = await this.queue.size();
      this._setGauge('current', 'queue_depth', size);
      if (size > this._metrics.current.peak_queue_depth) {
        this._setGauge('current', 'peak_queue_depth', size);
        this._setGauge('current', 'peak_queue_depth_at', new Date().toISOString());
      }
      // Update oldest item age
      const all = await this.queue.getAll();
      if (all.length > 0) {
        const oldest = all.reduce((min, i) => i.timestamp < min.timestamp ? i : min);
        this._setGauge('current', 'oldest_item_age_ms', Date.now() - oldest.timestamp);
      }
    } catch (e) {
      console.warn('SyncCoordinator: metric update failed (non-blocking)', e);
    }

    return item;
  }

  async getStatus() {
    const queueSize = await this.queue.size();
    const deadLetters = await this.queue.getDeadLetters();
    return {
      queue_size: queueSize,
      dead_letter_count: deadLetters.length,
      started: this._started,
    };
  }

  /**
   * S3: Get all metrics — read-only snapshot. Derived metrics computed on read.
   */
  getMetrics() {
    const m = this._metrics;
    const total = m.cumulative.total_enqueued || 1;

    return {
      current: { ...m.current },
      cumulative: { ...m.cumulative },
      timing: { ...m.timing },
      derived: {
        success_rate: Math.round((m.cumulative.total_synced / total) * 100),
        retry_exhaustion_rate: m.cumulative.total_synced + m.cumulative.total_dead_letter > 0
          ? Math.round((m.cumulative.total_dead_letter / (m.cumulative.total_synced + m.cumulative.total_dead_letter)) * 100)
          : 0,
        average_queue_wait_ms: m.cumulative.total_synced > 0
          ? Math.round(m.timing.total_drain_duration_ms / m.cumulative.total_synced)
          : 0,
        average_drain_duration_ms: m.cumulative.total_drains > 0
          ? Math.round(m.timing.total_drain_duration_ms / m.cumulative.total_drains)
          : 0,
      },
    };
  }

  /**
   * S3: Reset cumulative counters only — preserves current state.
   */
  resetMetrics() {
    this._metrics.cumulative = {
      total_enqueued: 0,
      total_synced: 0,
      total_retries: 0,
      total_dead_letter: 0,
      total_drains: 0,
    };
    this._metrics.timing.last_drain_duration_ms = 0;
    this._metrics.timing.total_drain_duration_ms = 0;
  }

  // --- Private metric helpers (FR-09: never throw) ---

  _increment(category, key) {
    try {
      this._metrics[category][key] = (this._metrics[category][key] || 0) + 1;
    } catch (e) {
      console.warn('SyncCoordinator: _increment failed', e);
    }
  }

  _addToCounter(category, key, amount) {
    try {
      this._metrics[category][key] = (this._metrics[category][key] || 0) + amount;
    } catch (e) {
      console.warn('SyncCoordinator: _addToCounter failed', e);
    }
  }

  _setGauge(category, key, value) {
    try {
      this._metrics[category][key] = value;
    } catch (e) {
      console.warn('SyncCoordinator: _setGauge failed', e);
    }
  }

  _recordTiming(key) {
    try {
      this._metrics.timing[key] = new Date().toISOString();
    } catch (e) {
      console.warn('SyncCoordinator: _recordTiming failed', e);
    }
  }
}

export const syncCoordinator = new SyncCoordinator();
export default syncCoordinator;
