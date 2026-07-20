/**
 * SafetySyncManager — Compatibility wrapper.
 * Delegates to SyncCoordinator (S2).
 * Preserved for backward compatibility.
 */

import syncCoordinator from './SyncCoordinator.js';

class SafetySyncManager {
  constructor() {
    this.started = false;
  }

  start() {
    if (this.started) return;
    syncCoordinator.start();
    this.started = true;
  }

  stop() {
    syncCoordinator.stop();
    this.started = false;
  }
}

export default SafetySyncManager;
