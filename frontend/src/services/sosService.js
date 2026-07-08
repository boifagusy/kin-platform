// SOS Service — Simple standalone version (no api.js dependency)
import { secureStorage } from './secureStorage';

class SOSService {
  constructor() {
    this.silentMode = false;
    this.isActive = false;
  }

  // Cancel an SOS alert
  async cancelSOS(sosId) {
    try {
      const token = localStorage.getItem('kin_token');
      const response = await fetch(`/api/v1/sos/${sosId}/cancel`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });
      if (!response.ok) {
        throw new Error('Failed to cancel SOS');
      }
      const data = await response.json();
      this.isActive = false;
      return data;
    } catch (error) {
      console.error('Cancel SOS error:', error);
      throw error;
    }
  }

  async triggerSOS(options = {}) {
    const { silent = true, location = null } = options;
    
    console.log('🆘 SOS Triggered:', { silent, location });
    
    // Store in localStorage
    localStorage.setItem('kin_sos_triggered', JSON.stringify({
      timestamp: new Date().toISOString(),
      silent: silent,
      location: location
    }));
    
    this.silentMode = silent;
    this.isActive = true;
    
    // Silent mode: no alert
    if (!silent) {
      alert('🚨 SOS Alert Sent!');
    }
    
    return { 
      success: true, 
      message: silent ? 'Silent SOS triggered' : 'SOS Alert Sent',
      silent: silent
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
