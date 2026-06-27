// Background location tracking service
class BackgroundLocationService {
  constructor() {
    this.isTracking = false;
    this.phone = null;
    this.locationInterval = null;
  }

  // Start tracking location
  start(phone) {
    console.log(`📍 Starting background location for ${phone}`);
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
    console.log('📍 Stopping background location');
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
        
        console.log(`📍 Location: ${location.lat}, ${location.lng}`);
        
        // Store location for SOS
        this.lastLocation = location;
        return location;
      }
    } catch (error) {
      console.error('❌ Location error:', error);
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
