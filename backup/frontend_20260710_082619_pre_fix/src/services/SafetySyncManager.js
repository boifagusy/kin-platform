import { LocationQueue } from './LocationQueue.js';
import { SyncQueue } from './SyncQueue.js';
import networkDetection from './NetworkDetection.js';

const API_BASE = import.meta.env.VITE_API_URL || '/api/v1';

class SafetySyncManager {
  constructor() {
    this.queue = new LocationQueue();
    this.syncQueue = null;
    this.cleanup = null;
    this.started = false;
  }

  start() {
    if (this.started) {
      console.log('🔄 SafetySyncManager already started');
      return;
    }

    console.log('🚀 SafetySyncManager starting...');

    this.syncQueue = new SyncQueue(this.queue, async (item) => {
      const endpoint = `${API_BASE}/${item.type === 'sos' ? 'sos' : 'checkin'}`;
      const response = await fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('kin_token')}`,
          'Accept': 'application/json'
        },
        body: JSON.stringify(item)
      });
      if (!response.ok) {
        throw new Error(`Upload failed: ${response.status}`);
      }
      return true;
    });

    // Sync on app start
    this.syncQueue.syncOnStart(10, 3000);

    // Sync on network reconnect
    this.cleanup = this.syncQueue.syncOnReconnect(10, 2000);

    // Also sync immediately if online
    if (networkDetection.isOnline()) {
      setTimeout(() => {
        this.syncQueue.drain(10, 500).catch(err => {
          console.warn('Initial sync drain failed:', err);
        });
      }, 1000);
    }

    this.started = true;
    console.log('✅ SafetySyncManager started');
  }

  stop() {
    if (this.cleanup) {
      this.cleanup();
      this.cleanup = null;
    }
    this.started = false;
    console.log('🔄 SafetySyncManager stopped');
  }
}

const manager = new SafetySyncManager();
export default manager;
