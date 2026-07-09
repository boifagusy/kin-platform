/**
 * NetworkDetection — Network state monitoring for KIN
 * 
 * Features:
 *   - Online/offline status detection
 *   - Network change event listeners
 *   - Connection quality monitoring
 *   - Connection type detection (WiFi, Cellular, None)
 * 
 * Usage:
 *   const network = new NetworkDetection();
 *   console.log(network.getStatus()); // { online: true, type: 'wifi', effectiveType: '4g' }
 *   network.onChange((status) => { console.log('Network changed:', status); });
 */
export class NetworkDetection {
  constructor() {
    this.status = {
      online: navigator.onLine,
      type: this._getConnectionType(),
      effectiveType: this._getEffectiveType(),
      downlink: this._getDownlink(),
      rtt: this._getRtt(),
    };
    this.listeners = [];
    this._boundHandlers = {
      online: this._handleOnline.bind(this),
      offline: this._handleOffline.bind(this),
      change: this._handleConnectionChange.bind(this),
    };
    this._connection = null;
    this._init();
  }

  /**
   * Initialize network listeners
   * @private
   */
  _init() {
    // Listen for online/offline events
    window.addEventListener('online', this._boundHandlers.online);
    window.addEventListener('offline', this._boundHandlers.offline);

    // Listen for connection changes (if available)
    if (navigator.connection) {
      this._connection = navigator.connection;
      this._connection.addEventListener('change', this._boundHandlers.change);
    }
  }

  /**
   * Handle online event
   * @private
   */
  _handleOnline() {
    this.status.online = true;
    this._updateConnectionInfo();
    this._notifyListeners();
    console.log('📶 Network: Online');
  }

  /**
   * Handle offline event
   * @private
   */
  _handleOffline() {
    this.status.online = false;
    this._updateConnectionInfo();
    this._notifyListeners();
    console.log('📶 Network: Offline');
  }

  /**
   * Handle connection change event
   * @private
   */
  _handleConnectionChange() {
    this._updateConnectionInfo();
    this._notifyListeners();
    console.log('📶 Network: Connection changed', this.status);
  }

  /**
   * Update connection information from navigator.connection
   * @private
   */
  _updateConnectionInfo() {
    if (navigator.connection) {
      this.status.type = this._getConnectionType();
      this.status.effectiveType = this._getEffectiveType();
      this.status.downlink = this._getDownlink();
      this.status.rtt = this._getRtt();
    }
  }

  /**
   * Get connection type (wifi, cellular, none, etc.)
   * @returns {string}
   */
  _getConnectionType() {
    if (!navigator.onLine) return 'none';
    if (!navigator.connection) return 'unknown';
    return navigator.connection.type || 'unknown';
  }

  /**
   * Get effective connection type (4g, 3g, 2g, slow-2g)
   * @returns {string}
   */
  _getEffectiveType() {
    if (!navigator.connection) return 'unknown';
    return navigator.connection.effectiveType || 'unknown';
  }

  /**
   * Get downlink speed in Mbps
   * @returns {number}
   */
  _getDownlink() {
    if (!navigator.connection) return 0;
    return navigator.connection.downlink || 0;
  }

  /**
   * Get round-trip time in ms
   * @returns {number}
   */
  _getRtt() {
    if (!navigator.connection) return 0;
    return navigator.connection.rtt || 0;
  }

  /**
   * Get current network status
   * @returns {{
   *   online: boolean,
   *   type: string,
   *   effectiveType: string,
   *   downlink: number,
   *   rtt: number
   * }}
   */
  getStatus() {
    return { ...this.status };
  }

  /**
   * Check if online
   * @returns {boolean}
   */
  isOnline() {
    return this.status.online;
  }

  /**
   * Check if offline
   * @returns {boolean}
   */
  isOffline() {
    return !this.status.online;
  }

  /**
   * Check if on WiFi
   * @returns {boolean}
   */
  isWifi() {
    return this.status.type === 'wifi';
  }

  /**
   * Check if on Cellular
   * @returns {boolean}
   */
  isCellular() {
    return this.status.type === 'cellular';
  }

  /**
   * Subscribe to network changes
   * @param {Function} callback - Called with status object on change
   * @returns {Function} Unsubscribe function
   */
  onChange(callback) {
    this.listeners.push(callback);
    return () => {
      this.listeners = this.listeners.filter(cb => cb !== callback);
    };
  }

  /**
   * Notify all listeners of status change
   * @private
   */
  _notifyListeners() {
    const status = this.getStatus();
    for (const listener of this.listeners) {
      try {
        listener(status);
      } catch (error) {
        console.error('NetworkDetection listener error:', error);
      }
    }
  }

  /**
   * Clean up event listeners
   */
  destroy() {
    window.removeEventListener('online', this._boundHandlers.online);
    window.removeEventListener('offline', this._boundHandlers.offline);
    if (this._connection) {
      this._connection.removeEventListener('change', this._boundHandlers.change);
    }
    this.listeners = [];
  }

  /**
   * Wait for stable connection (multiple checks)
   * @param {number} checks - Number of checks to perform
   * @param {number} intervalMs - Interval between checks in ms
   * @returns {Promise<boolean>} True if stable online
   */
  async waitForStableConnection(checks = 3, intervalMs = 1000) {
    let stableChecks = 0;
    for (let i = 0; i < checks; i++) {
      if (this.isOnline()) {
        stableChecks++;
      } else {
        stableChecks = 0;
      }
      if (stableChecks >= Math.ceil(checks / 2)) {
        return true;
      }
      await new Promise(resolve => setTimeout(resolve, intervalMs));
    }
    return false;
  }
}

// Singleton
const networkDetection = new NetworkDetection();
export default networkDetection;
