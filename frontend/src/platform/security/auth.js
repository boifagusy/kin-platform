/**
 * Platform Security Auth
 * 
 * Authentication utilities and state management.
 * Provides methods to check authentication status.
 */

import { logger } from '../core/logger';
import { hasToken, getToken } from './token';

/**
 * Check if user is authenticated
 * @returns {boolean} User has valid token
 */
export function isAuthenticated() {
  return hasToken();
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
 * Note: This is a simple existence check.
 * Actual expiry validation happens at API level.
 * @returns {boolean} Token exists
 */
export function hasValidToken() {
  return hasToken();
}

/**
 * Log authentication state
 * Used for debugging
 */
export function logAuthState() {
  const state = getAuthState();
  logger.debug('Auth state:', state);
}

export default {
  isAuthenticated,
  getAuthState,
  hasValidToken,
  logAuthState,
};
