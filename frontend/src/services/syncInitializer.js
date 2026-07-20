/**
 * syncInitializer — Compatibility wrapper.
 * Delegates to SyncCoordinator (S2).
 * Preserved for backward compatibility.
 */

import syncCoordinator from './SyncCoordinator.js';

export function initializeSync() {
  syncCoordinator.start();
  return () => syncCoordinator.stop();
}

export default initializeSync;
