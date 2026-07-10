import { LocationQueue } from './LocationQueue.js';
import { SyncQueue } from './SyncQueue.js';
import networkDetection from './NetworkDetection.js';

const API_BASE = import.meta.env.VITE_API_URL || '/api/v1';

// Result types
export const RESULT = {
  SENT: 'SENT',
  QUEUED: 'QUEUED',
  FAILED: 'FAILED'
};

class SafetyService {
  constructor() {
    this.queue = new LocationQueue();
  }

  /**
   * Process any safety action (SOS, Check-in, Silent SOS, etc.)
   * This is the single source of truth for all safety actions.
   */
  async processSafetyAction(type, payload) {
    console.log(`🛡️ Safety action: ${type}`, payload);

    // 1. ALWAYS enqueue first
    await this.queue.enqueue(payload);
    console.log(`📦 ${type} enqueued`);
    console.log(`📡 isTrulyOnline: ${trulyOnline}`);

    // 2. Try to sync immediately if online
    const trulyOnline = await networkDetection.isTrulyOnline();
    console.log(`📡 isTrulyOnline: ${trulyOnline}`);

    if (trulyOnline) {
      try {
        const syncQueue = new SyncQueue(this.queue, async (item) => {
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

        const result = await syncQueue.drain(1, 500);
        if (result.synced > 0) {
          console.log(`✅ ${type} synced immediately`);
    console.log(`📤 result:`, result);
          return {
            state: RESULT.SENT,
            queued: false,
            synced: true,
            type: type
          };
        }
      } catch (error) {
        console.warn(`${type} sync failed, item remains queued:`);
    console.warn(`  error:`, error.message);
    console.warn(`  stack:`, error.stack);
      }
    }

    return {
      state: RESULT.QUEUED,
      queued: true,
      synced: false,
      type: type
    };
  }

  // Public API
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
    return this.processSafetyAction('sos', payload);
  }

  async checkIn(phone, locationData = null, batteryLevel = null) {
    const payload = {
      type: 'checkin',
      phone,
      status: 'safe',
      latitude: locationData?.latitude || null,
      longitude: locationData?.longitude || null,
      battery_level: batteryLevel || null,
      timestamp: Date.now()
    };
    return this.processSafetyAction('checkin', payload);
  }

  async triggerSilentSOS(phone, locationData = null, batteryLevel = null) {
    return this.triggerSOS(phone, locationData, batteryLevel, { silent: true });
  }
}

const safetyService = new SafetyService();
export default safetyService;
