// SOS Service with LocationQueue integration — Always queue first, then sync
import { LocationQueue } from './LocationQueue.js';
import networkDetection from './NetworkDetection.js';

const API_BASE = import.meta.env.VITE_API_URL || '/api/v1';

class SOSService {
  constructor() {
    this.silentMode = false;
    this.isActive = false;
    this.queue = new LocationQueue();
  }

  async triggerSOS(phone, locationData = null, batteryLevel = null, options = {}) {
    const { silent = false } = options;


    const payload = {
      type: 'sos',
      phone,
      latitude: locationData?.latitude || null,
      longitude: locationData?.longitude || null,
      accuracy: locationData?.accuracy || null,
      battery_level: batteryLevel || null,
      silent: silent,
      timestamp: Date.now()
    };

    this.silentMode = silent;
    this.isActive = true;

    // Store in localStorage for backward compatibility
    localStorage.setItem('kin_sos_triggered', JSON.stringify({
      timestamp: new Date().toISOString(),
      silent: silent,
      location: locationData
    }));

    // ALWAYS QUEUE FIRST (like test harness)
    await this.queue.enqueue(payload);

    // Then try to sync immediately if online
    const trulyOnline = await networkDetection.isTrulyOnline();
    
    if (trulyOnline) {
      try {
        // Import SyncQueue dynamically
        const { SyncQueue } = await import('./SyncQueue.js');
        const syncQueue = new SyncQueue(this.queue, async (item) => {
          const response = await fetch(`${API_BASE}/sos`, {
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
        
        const result = await syncQueue.drain(1, 500);
        if (result.synced > 0) {
          return {
            success: true,
            queued: false,
            synced: true,
            silent,
            message: silent ? 'Silent SOS triggered' : 'SOS Alert Sent'
          };
        }
      } catch (error) {
        console.warn('SOS sync failed, item remains queued:', error);
      }
    }

    return {
      success: true,
      queued: true,
      silent,
      message: silent ? 'Silent SOS saved for when online' : 'SOS saved for when online'
    };
  }

  isSilentSOSActive() {
    return this.isActive;
  }

  clearSilentSOS() {
    this.isActive = false;
    this.silentMode = false;
    localStorage.removeItem('kin_sos_triggered');
  }
}

const sosService = new SOSService();
export default sosService;
