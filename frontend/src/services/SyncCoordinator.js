/**
 * SyncCoordinator — Single owner of queue lifecycle and synchronization.
 * Singleton (FR-09). FR-08: Exactly one active instance for the shared queue key.
 *
 * Startup order (Condition 3):
 *   App boot → SyncCoordinator.start() → restore queue → register reconnect → begin draining
 */

import { LocationQueue } from './LocationQueue.js';
import { SyncQueue } from './SyncQueue.js';
import networkDetection from './NetworkDetection.js';

const API_BASE = import.meta.env.VITE_API_URL || '/api/v1';

let instance = null;

class SyncCoordinator {
  constructor() {
    if (instance) {
      return instance;
    }

    this.queue = new LocationQueue(null, 'sync_coordinator_queue');
    this.syncQueue = null;
    this._cleanup = null;
    this._started = false;

    instance = this;
  }

  /**
   * Initialize sync: restore queue, register reconnect, begin draining.
   * Safe to call multiple times — only starts once.
   */
  start() {
    if (this._started) return;

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

    // Startup drain
    this.syncQueue.syncOnStart(10, 3000);

    // Reconnect drain
    this._cleanup = this.syncQueue.syncOnReconnect(10, 2000);

    // Immediate drain if online
    if (networkDetection.isOnline()) {
      setTimeout(() => {
        this.syncQueue.drain(10, 500).catch(err => {
          console.warn('SyncCoordinator: initial drain failed:', err);
        });
      }, 1000);
    }

    this._started = true;
  }

  /**
   * Stop sync and cleanup listeners.
   */
  stop() {
    if (this._cleanup) {
      this._cleanup();
      this._cleanup = null;
    }
    this._started = false;
  }

  /**
   * Enqueue an item for sync (public API for all services).
   * @param {string} type - 'sos' | 'checkin' | 'safe_zone' | 'trusted_contact' | 'location'
   * @param {object} payload
   */
  async enqueue(type, payload) {
    const item = {
      type,
      payload,
      timestamp: Date.now(),
    };
    await this.queue.enqueue(item);
    return item;
  }

  /**
   * Get queue status for diagnostics.
   */
  async getStatus() {
    const queueSize = await this.queue.size();
    const deadLetters = await this.queue.getDeadLetters();
    return {
      queue_size: queueSize,
      dead_letter_count: deadLetters.length,
      started: this._started,
    };
  }
}

// Singleton export
export const syncCoordinator = new SyncCoordinator();
export default syncCoordinator;
