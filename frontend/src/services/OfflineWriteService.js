/**
 * OfflineWriteService v2.0 — Platform-wide offline-first write abstraction.
 * ADR: ADR-0010
 */

import { LocationQueue } from './LocationQueue.js';
import { SyncQueue } from './SyncQueue.js';
import networkDetection from './NetworkDetection.js';

const API_BASE = import.meta.env.VITE_API_URL || '/api/v1';
const QUEUE_VERSION = 1;

export const RESULT = {
  SENT: 'SENT',
  QUEUED: 'QUEUED',
  FAILED: 'FAILED',
};

class OfflineWriteService {
  constructor() {
    this.queue = new LocationQueue(null, 'offline_write_queue');
    this._startSyncOnReconnect();
  }

  /**
   * Single network send — used by both online and sync paths.
   */
  async _send(item) {
    const url = `${API_BASE}/${item.endpoint}`;
    const response = await fetch(url, {
      method: item.method,
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('kin_token') || localStorage.getItem('sanctum_token') || ''}`,
        'Accept': 'application/json',
      },
      body: JSON.stringify(item.payload),
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      const error = new Error(data.message || data.error || `HTTP ${response.status}`);
      error.status = response.status;
      error.data = data;
      throw error;
    }

    return { status: response.status, data };
  }

  /**
   * Public write API.
   * @param {string} type — 'checkin' | 'sos' | 'safe_zone' | 'trusted_contact'
   * @param {object} payload — The data to send
   * @param {string} method — HTTP method (default: 'POST')
   */
  async write(type, payload, method = 'POST') {
    const item = {
      version: QUEUE_VERSION,
      type,
      method,
      endpoint: this._endpoint(type),
      payload,
      timestamp: Date.now(),
    };

    await this.queue.enqueue(item);

    if (await networkDetection.isTrulyOnline()) {
      try {
        const result = await this._send(item);
        await this.queue.dequeue();
        return { state: RESULT.SENT, ...result };
      } catch (error) {
        if (this._isRetryable(error)) {
          console.warn(`OfflineWrite: ${type} queued (${error.status})`);
          return { state: RESULT.QUEUED, queued: true };
        }
        await this.queue.dequeue();
        return { state: RESULT.FAILED, error: error.message };
      }
    }

    return { state: RESULT.QUEUED, queued: true };
  }

  _endpoint(type) {
    const map = { checkin: 'checkin', sos: 'sos', safe_zone: 'safe-zones', trusted_contact: 'trusted-contacts' };
    return map[type] || type;
  }

  _isRetryable(error) {
    const status = error.status || 0;
    return status === 0 || status >= 500 || status === 429;
  }

  _startSyncOnReconnect() {
    this._cleanup = new SyncQueue(this.queue, async (item) => {
      await this._send(item);
      return true;
    }).syncOnReconnect(10, 2000);
  }


  async syncNow() {
    const count = await this.queue.size();
    if (count === 0) return { synced: 0 };
    const sq = new SyncQueue(this.queue, async (item) => {
      await this._send(item);
      return true;
    });
    return sq.drain(count, 500);
  }

  async pendingCount() {
    return this.queue.size();
  }
}

const offlineWriteService = new OfflineWriteService();
export default offlineWriteService;
