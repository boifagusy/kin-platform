/**
 * Platform Security Session
 * 
 * Manages session state and lifecycle.
 * Tracks authentication state, session expiry, and logout.
 */

import { logger } from '../core/logger';
import { getToken, setToken, clearToken, getPhone, setPhone } from './token';

class SessionManager {
  constructor() {
    this.listeners = [];
    this.sessionTimeout = null;
  }

  /**
   * Initialize session with token and phone
   * @param {string} token - Bearer token
   * @param {string} phone - Phone number
   * @returns {boolean} Success
   */
  initializeSession(token, phone) {
    const success = setToken(token) && setPhone(phone);
    if (success) {
      logger.info('Session initialized', { phone });
      this.notifyListeners({ type: 'SESSION_INITIALIZED' });
    }
    return success;
  }

  /**
   * Get current session data
   * @returns {Object} { token, phone, authenticated }
   */
  getSession() {
    return {
      token: getToken(),
      phone: getPhone(),
      authenticated: !!getToken(),
    };
  }

  /**
   * Check if session is active
   * @returns {boolean} Session has valid token
   */
  isSessionActive() {
    return !!getToken();
  }

  /**
   * Terminate session
   * @returns {boolean} Success
   */
  terminateSession() {
    clearToken();
    this.clearSessionTimeout();
    logger.info('Session terminated');
    this.notifyListeners({ type: 'SESSION_TERMINATED' });
    return true;
  }

  /**
   * Register listener for session changes
   * @param {Function} callback - Called when session changes
   */
  addListener(callback) {
    this.listeners.push(callback);
  }

  /**
   * Unregister listener
   * @param {Function} callback - Listener to remove
   */
  removeListener(callback) {
    this.listeners = this.listeners.filter(l => l !== callback);
  }

  /**
   * Notify all listeners of session change
   * @param {Object} event - Event data
   */
  notifyListeners(event) {
    this.listeners.forEach(callback => {
      try {
        callback(event);
      } catch (err) {
        logger.error('Session listener error', err);
      }
    });
  }

  /**
   * Set session timeout (for future token refresh logic)
   * @param {number} ms - Milliseconds until timeout
   */
  setSessionTimeout(ms) {
    this.clearSessionTimeout();
    this.sessionTimeout = setTimeout(() => {
      logger.warn('Session timeout reached');
      this.notifyListeners({ type: 'SESSION_TIMEOUT' });
    }, ms);
  }

  /**
   * Clear session timeout
   */
  clearSessionTimeout() {
    if (this.sessionTimeout) {
      clearTimeout(this.sessionTimeout);
      this.sessionTimeout = null;
    }
  }
}

export const sessionManager = new SessionManager();

export default {
  sessionManager,
};
