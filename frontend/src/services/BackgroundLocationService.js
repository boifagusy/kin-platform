import { LocationQueue } from "./LocationQueue.js";
import { SyncQueue } from "./SyncQueue.js";
import networkDetection from "./NetworkDetection.js";

// Background location tracking service
class BackgroundLocationService {
  constructor() {
    this.isTracking = false;
    this.phone = null;
    this.locationInterval = null;
    this.locationQueue = new LocationQueue();
    this.syncQueue = null;
    this.cleanupSync = null;
    this.networkUnsubscribe = null;
  }

  // Start tracking location
  start(phone) {
    console.log(`\ud83d\udccd Starting background location for ${phone}`);
    this.phone = phone;
    this.isTracking = true;

    // Initialize sync queue
    this.syncQueue = new SyncQueue(
      this.locationQueue,
      async (item) => {
        // TODO: Replace with actual API endpoint
        console.log('\ud83d\udce4 Syncing location:', item);
        // Mock upload - replace with real API call
        // await api.uploadLocation(item);
        return true;
      }
    );

    // Sync on start
    this.syncQueue.syncOnStart(10, 3000).catch(err => {
      console.warn('Initial sync failed:', err);
    });

    // Sync on reconnect
    this.cleanupSync = this.syncQueue.syncOnReconnect(10, 2000);

    // Subscribe to network changes for logging
    this.networkUnsubscribe = networkDetection.onChange((status) => {
      console.log(`\ud83d\udcf6 Network: ${status.online ? 'Online' : 'Offline'} (${status.effectiveType || 'unknown'})`);
    });

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

    if (this.networkUnsubscribe) {
      this.networkUnsubscribe();
      this.networkUnsubscribe = null;
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

        // Enqueue location if online, otherwise queue for later
        if (networkDetection.isOnline()) {
          // Try to sync immediately
          try {
            await this.syncQueue.drain(10, 500);
          } catch (err) {
            console.warn('Sync drain failed, enqueueing:', err);
            await this.locationQueue.enqueue(location);
          }
        } else {
          // Offline — queue for later
          console.log('\ud83d\udcf4 Offline — queuing location');
          await this.locationQueue.enqueue(location);
        }

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

  /**
   * Get current queue size
   * @returns {Promise<number>}
   */
  async getQueueSize() {
    return await this.locationQueue.size();
  }
}

// Export singleton
export default new BackgroundLocationService();
