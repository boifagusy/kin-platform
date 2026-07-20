/**
 * sosService — Delegates queue operations to SyncCoordinator (S2.1).
 * Public API unchanged (FR-08).
 */

import syncCoordinator from './SyncCoordinator.js';

class SosService {
  async trigger(payload) {
    return await syncCoordinator.enqueue('sos', payload);
  }
}

export default new SosService();
