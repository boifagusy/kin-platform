// SOS Service with LocationQueue integration
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
    
    console.log('🆘 SOS Triggered:', { phone, silent, locationData });

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

    // Try to send immediately if online
    if (networkDetection.isOnline()) {
      try {
        const response = await fetch(`${API_BASE}/sos`, {
          method: 'POST',
          headers: { 
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('kin_token')}`,
            'Accept': 'application/json'
          },
          body: JSON.stringify(payload)
        });
        const data = await response.json();
        if (data.success) {
          console.log('✅ SOS sent immediately');
          return {
            success: true,
            queued: false,
            data,
            silent,
            message: silent ? 'Silent SOS triggered' : 'SOS Alert Sent'
          };
        }
      } catch (error) {
        console.warn('SOS send failed, queueing:', error);
      }
    }

    // Queue for later
    await this.queue.enqueue(payload);
    console.log('📦 SOS queued for later sync');
    
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
