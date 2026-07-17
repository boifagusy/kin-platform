import { LocationQueue } from './LocationQueue.js';
import { SyncQueue } from './SyncQueue.js';
import networkDetection from './NetworkDetection.js';

const API_BASE = import.meta.env.VITE_API_URL || '/api/v1';

/**
 * Initialize auto-sync for the LocationQueue
 * Call this once when the app starts
 */
export function initializeSync() {
  const queue = new LocationQueue();
  
  // Create sync queue with upload function
  const syncQueue = new SyncQueue(queue, async (item) => {
    
    // Determine the endpoint based on item type
    let endpoint = '';
    if (item.type === 'sos') {
      endpoint = `${API_BASE}/sos`;
    } else if (item.type === 'checkin') {
      endpoint = `${API_BASE}/checkin`;
    } else {
      endpoint = `${API_BASE}/sync`;
    }
    

    const response = await fetch(endpoint, {
        method: 'POST',
        headers: headers,
      method: 'POST',
      const token = localStorage.getItem('kin_token');
      const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      };
      if (token) {
        headers['Authorization'] = `Bearer ${token}`;
      }
      body: JSON.stringify(item),
    });
    
    if (!response.ok) {
      throw new Error(`Upload failed: ${response.status}`);
    }
    
    return true;
  });

  // 1. Sync on app start (after 3 seconds)
  syncQueue.syncOnStart(10, 3000);

  // 2. Sync on network reconnect (after 2 seconds)
  const cleanupReconnect = syncQueue.syncOnReconnect(10, 2000);

  // 3. Also sync immediately if online
  if (networkDetection.isOnline()) {
    setTimeout(() => {
      syncQueue.drain(10, 500).catch(err => {
        console.warn('Initial sync drain failed:', err);
      });
    }, 1000);
  }


  return () => {
    cleanupReconnect();
  };
}

export default initializeSync;
