import { LocationQueue } from './LocationQueue.js';
import { SyncQueue } from './SyncQueue.js';
import networkDetection from './NetworkDetection.js';

const API_BASE = import.meta.env.VITE_API_URL || '/api/v1';

export const RESULT = {
  SENT: 'SENT',
  QUEUED: 'QUEUED',
  FAILED: 'FAILED'
};

class SafetyService {
  constructor() {
    this.queue = new LocationQueue();
  }

  async processSafetyAction(type, payload) {
    console.log('🔍 STEP 1 - processSafetyAction() called', { type, payload });

    // 1. ALWAYS enqueue first
    await this.queue.enqueue(payload);
    console.log('🔍 STEP 2 - queued', { type });
    const afterEnqueue = await this.queue.size();
    console.log('🔍 STEP 2b - queue size after enqueue:', afterEnqueue);

    // 2. Try to sync immediately if online
    const trulyOnline = await networkDetection.isTrulyOnline();
    console.log('🔍 STEP 3 - isTrulyOnline:', trulyOnline);

    if (trulyOnline) {
      try {
        console.log('🔍 STEP 4 - before drain');
        const beforeDrain = await this.queue.size();
        console.log('🔍 STEP 4b - queue size before drain:', beforeDrain);

        const syncQueue = new SyncQueue(this.queue, async (item) => {
          const endpoint = `${API_BASE}/${item.type === 'sos' ? 'sos' : 'checkin'}`;
          console.log('📤 Syncing to:', endpoint);
          const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${localStorage.getItem('kin_token')}`,
              'Accept': 'application/json'
            },
            body: JSON.stringify(item)
          });
          console.log('📤 Response status:', response.status);
          if (!response.ok) {
            throw new Error(`Upload failed: ${response.status}`);
          }
          return true;
        });

        const result = await syncQueue.drain(1, 500);
        console.log('🔍 STEP 5 - drain result:', JSON.stringify(result));
        const afterDrain = await this.queue.size();
        console.log('🔍 STEP 5b - queue size after drain:', afterDrain);

        if (result.synced > 0) {
          console.log(`✅ ${type} synced immediately`);
          return {
            state: RESULT.SENT,
            queued: false,
            synced: true,
            type: type
          };
        } else {
          console.log(`⚠️ ${type} result.synced is 0`);
          if (afterDrain < beforeDrain && afterDrain === 0) {
            console.log(`✅ ${type} was sent but not counted`);
            return {
              state: RESULT.SENT,
              queued: false,
              synced: true,
              type: type
            };
          }
        }
      } catch (error) {
        console.warn(`${type} sync failed:`, error);
      }
    }

    console.log('🔍 STEP 6 - returning QUEUED');
    return {
      state: RESULT.QUEUED,
      queued: true,
      synced: false,
      type: type
    };
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
