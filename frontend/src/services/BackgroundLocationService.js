import { LocationQueue } from "./LocationQueue.js";
import { SyncQueue } from "./SyncQueue.js";

// Background location tracking service
class BackgroundLocationService {
  constructor() {
    this.isTracking = false;
    this.phone = null;
    this.locationInterval = null;
    this.syncQueue = null;
    this.cleanupSync = null;
  }

  // Start tracking location
  start(phone) {
    // Initialize sync queue
    this.syncQueue = new SyncQueue(
      new LocationQueue(),
      async (item) => {
        console.log('\ud83d\udce4 Syncing location:', item);
        // TODO: Call API endpoint
        return true;
      }
    );

    // Sync on start
    this.syncQueue.syncOnStart(10, 3000).catch(err => {
      console.warn('Initial sync failed:', err);
    });

    // Sync on reconnect
    this.cleanupSync = this.syncQueue.syncOnReconnect(10, 2000);

    console.log(`\ud83d\udccd Starting background location for ${phone}`);
    this.phone = phone;
    this.isTracking = true;

    // Get initial location
    this.getCurrentLocation();

    // Start interval (every 30 seconds)
    this.locationInterval = setInterval(() => {
      this.getCurrentLocation();
    }, 30000);

    return this;
  }

  // Stop tracking
  stop() {
    console.log('\ud83d\udccd Stopping background location');

    // Cleanup sync listeners
    if (this.cleanupSync) {
      this.cleanupSync();
      this.cleanupSync = null;
    }

    this.isTracking = false;
    if (this.locationInterval) {
      clearInterval(this.locationInterval);
      this.locationInterval = null;
    }
    return this;
  }

  // Get current location
  async getCurrentLocation() {
    try {
      // Use browser geolocation
      if (navigator.geolocation) {
        const position = await new Promise((resolve, reject) => {
          navigator.geolocation.getCurrentPosition(resolve, reject, {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 10000
          });
        });

        const location = {
          lat: position.coords.latitude,
          lng: position.coords.longitude,
          accuracy: position.coords.accuracy,
          timestamp: new Date().toISOString()
        };

        console.log(`\ud83d\udccd Location: ${location.lat}, ${location.lng}`);

        // Store location for SOS
        this.lastLocation = location;
        return location;
      }
    } catch (error) {
      console.error('\u274c Location error:', error);
    }
    return null;
  }

  // Get last known location
  getLastLocation() {
    return this.lastLocation || null;
  }
}

// Export singleton
export default new BackgroundLocationService();
