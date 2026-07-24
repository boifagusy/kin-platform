/**
 * Platform Security Auth (Enhanced)
 * 
 * Authentication utilities and state management.
 * Provides methods to check authentication status and guard access.
 */

import { logger } from '../core/logger';
import { hasToken, getToken, isTokenExpired } from './token';
import { sessionManager } from './session';

/**
 * Check if user is authenticated
 * @returns {boolean} User has valid token
 */
export function isAuthenticated() {
  return hasToken() && !isTokenExpired();
}

/**
 * Get current authentication state
 * @returns {Object} { authenticated: boolean, token: string|null }
 */
export function getAuthState() {
  const token = getToken();
  return {
    authenticated: !!token,
    token,
  };
}

/**
 * Check if token exists but might be expired
 * @returns {boolean} Token exists
 */
export function hasValidToken() {
  return hasToken() && !isTokenExpired();
}

/**
 * Guard: Require authentication
 * Call this to protect features that need auth
 * @param {string} feature - Feature name for logging
 * @throws {Error} If not authenticated
 */
export function requireAuth(feature = 'Unknown') {
  if (!isAuthenticated()) {
    logger.warn(`Unauthorized access to ${feature}`);
    throw new Error(`Unauthorized: ${feature} requires authentication`);
  }
}

/**
 * Log authentication state (for debugging)
 */
export function logAuthState() {
  const state = getAuthState();
  logger.debug('Auth state:', state);
}

/**
 * Subscribe to authentication state changes
 * @param {Function} callback - Called when auth state changes
 * @returns {Function} Unsubscribe function
 */
export function onAuthStateChange(callback) {
  sessionManager.addListener((event) => {
    if (event.type === 'SESSION_INITIALIZED' || event.type === 'SESSION_TERMINATED') {
      callback(getAuthState());
    }
  });

  return () => {
    sessionManager.removeListener(callback);
  };
}

export default {
  isAuthenticated,
  getAuthState,
  hasValidToken,
  requireAuth,
  logAuthState,
  onAuthStateChange,
};
