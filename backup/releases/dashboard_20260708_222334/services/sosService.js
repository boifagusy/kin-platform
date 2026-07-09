// SOS Service — Simple standalone version (no api.js dependency)
import { secureStorage } from './secureStorage';

class SOSService {
  constructor() {
    this.silentMode = false;
    this.isActive = false;
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
