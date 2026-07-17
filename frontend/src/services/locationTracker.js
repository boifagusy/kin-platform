const API_BASE = import.meta.env.VITE_API_URL;

class LocationTracker {
  constructor() {
    this.watchId = null;
    this.currentLocation = null;
    this.lastUpdate = null;
    this.isTracking = false;
    this.listeners = [];
    this.phone = null;
    this.accuracyThreshold = 50; // meters
    this.updateInterval = 5000; // 5 seconds
  }

  // Start tracking location
  startTracking(phone) {
    if (!phone) {
      console.error('LocationTracker: Phone required');
      return;
    }

    if (this.isTracking) {
      return;
    }

    this.phone = phone;

    if (!navigator.geolocation) {
      console.error('LocationTracker: Geolocation not supported');
      return;
    }


    this.watchId = navigator.geolocation.watchPosition(
      this.handlePositionUpdate.bind(this),
      this.handleError.bind(this),
      {
        enableHighAccuracy: true,
        timeout: 15000,
        maximumAge: 0,
      }
    );

    this.isTracking = true;
  }

  // Stop tracking
  stopTracking() {
    if (this.watchId) {
      navigator.geolocation.clearWatch(this.watchId);
      this.watchId = null;
      this.isTracking = false;
    }
  }

  // Handle position update
  handlePositionUpdate(position) {
    const { latitude, longitude, accuracy, speed, heading } = position.coords;
    const timestamp = position.timestamp;

    const locationData = {
      latitude,
      longitude,
      accuracy: Math.round(accuracy),
      speed: speed || 0,
      heading: heading || 0,
      timestamp: timestamp,
      age: Date.now() - timestamp,
    };

    this.currentLocation = locationData;
    this.lastUpdate = Date.now();


    // Send to backend
    this.sendLocationToBackend(locationData);

    // Notify listeners
    this.notifyListeners(locationData);
  }

  // Handle error
  handleError(error) {
    console.error('LocationTracker: Error:', error);
  }

  // Send location to backend
  async sendLocationToBackend(locationData) {
    if (!this.phone) return;

    try {
      const response = await fetch(`${API_BASE}/location`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          phone: this.phone,
          latitude: locationData.latitude,
          longitude: locationData.longitude,
          accuracy: locationData.accuracy,
          speed: locationData.speed,
          heading: locationData.heading,
          timestamp: locationData.timestamp,
        }),
      });

      const data = await response.json();
      if (!data.success) {
        console.warn('LocationTracker: Backend error:', data.error);
      }
    } catch (error) {
      console.error('LocationTracker: Failed to send location:', error);
    }
  }

  // Get current location (cache)
  getCurrentLocation() {
    return this.currentLocation;
  }

  // Get location with freshness
  getLocationWithFreshness() {
    if (!this.currentLocation) {
      return null;
    }

    const age = Date.now() - this.lastUpdate;
    return {
      ...this.currentLocation,
      age: age,
      ageHuman: this.formatAge(age),
      isFresh: age < 60000, // less than 1 minute
    };
  }

  // Format age
  formatAge(ms) {
    const seconds = Math.floor(ms / 1000);
    if (seconds < 60) return `${seconds} sec ago`;
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes} min ago`;
    const hours = Math.floor(minutes / 60);
    return `${hours} hr ago`;
  }

  // Listen for location updates
  addListener(callback) {
    this.listeners.push(callback);
  }

  removeListener(callback) {
    this.listeners = this.listeners.filter(cb => cb !== callback);
  }

  notifyListeners(data) {
    this.listeners.forEach(callback => {
      try {
        callback(data);
      } catch (err) {
        console.error('LocationTracker: Listener error:', err);
      }
    });
  }
}

// Singleton instance
const locationTracker = new LocationTracker();
export default locationTracker;
