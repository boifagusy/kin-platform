/**
 * SafetyService — Delegates queue operations to SyncCoordinator (S2.1).
 * Public API unchanged (FR-08).
 */

import syncCoordinator from './SyncCoordinator.js';

class SafetyService {
  async recordEvent(type, payload) {
    return await syncCoordinator.enqueue(type, payload);
  }
}

export default new SafetyService();
