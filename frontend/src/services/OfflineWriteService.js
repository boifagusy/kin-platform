/**
 * OfflineWriteService v2.1 — Delegates queue operations to SyncCoordinator (S2.1).
 * Public API unchanged (FR-08).
 * ADR: ADR-0010
 */

import syncCoordinator from './SyncCoordinator.js';
import networkDetection from './NetworkDetection.js';

const API_BASE = import.meta.env.VITE_API_URL || '/api/v1';

export const RESULT = {
  SENT: 'SENT',
  QUEUED: 'QUEUED',
  FAILED: 'FAILED',
};

class OfflineWriteService {
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

  async write(type, payload, method = 'POST') {
    const item = {
      type,
      method,
      endpoint: this._endpoint(type),
      payload,
      timestamp: Date.now(),
    };

    await syncCoordinator.enqueue(type, payload);

    if (await networkDetection.isTrulyOnline()) {
      try {
        const result = await this._send(item);
        return { state: RESULT.SENT, ...result };
      } catch (error) {
        if (this._isRetryable(error)) {
          return { state: RESULT.QUEUED, queued: true };
        }
        return { state: RESULT.FAILED, error: error.message };
      }
    }

    return { state: RESULT.QUEUED, queued: true };
  }

  _endpoint(type) {
    const map = { checkin: 'checkin', sos: 'sos', safe_zone: 'safe-zones', trusted_contact: 'trusted-contacts' };
    return map[type] || 'sync';
  }

  _isRetryable(error) {
    return error.status >= 500 || error.status === 429 || error.status === 0;
  }

  async flushAll() {
    const status = await syncCoordinator.getStatus();
    return { queued: status.queue_size, dead: status.dead_letter_count };
  }

  async getQueueSize() {
    const status = await syncCoordinator.getStatus();
    return status.queue_size;
  }

  // S2.1-P1: Compatibility method for SyncStatus component
  async pendingCount() {
    return await this.getQueueSize();
  }
}

export default OfflineWriteService;
