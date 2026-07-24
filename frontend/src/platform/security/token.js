/**
 * Platform Security Token (Enhanced)
 * 
 * Manages token storage, retrieval, and expiry tracking.
 * Provides single source of truth for authentication state.
 */

import { config } from '../core/config';
import { logger } from '../core/logger';

// Token metadata storage key
const TOKEN_METADATA_KEY = 'kin_token_metadata';

/**
 * Store authentication token with metadata
 * @param {string} token - Bearer token
 * @param {Object} metadata - Optional metadata (expiresAt, etc.)
 * @returns {boolean} Success
 */
export function setToken(token, metadata = {}) {
  try {
    if (!token) {
      localStorage.removeItem(config.storage.tokenKey);
      localStorage.removeItem(TOKEN_METADATA_KEY);
      logger.info('Token cleared');
      return true;
    }
    localStorage.setItem(config.storage.tokenKey, token);
    
    // Store metadata if provided
    if (Object.keys(metadata).length > 0) {
      localStorage.setItem(TOKEN_METADATA_KEY, JSON.stringify({
        storedAt: new Date().toISOString(),
        ...metadata,
      }));
    }
    
    logger.info('Token stored');
    return true;
  } catch (err) {
    logger.error('Failed to store token', err);
    return false;
  }
}

/**
 * Retrieve authentication token
 * @returns {string|null} Bearer token or null
 */
export function getToken() {
  try {
    return localStorage.getItem(config.storage.tokenKey);
  } catch (err) {
    logger.error('Failed to retrieve token', err);
    return null;
  }
}

/**
 * Get token metadata
 * @returns {Object|null} Token metadata or null
 */
export function getTokenMetadata() {
  try {
    const data = localStorage.getItem(TOKEN_METADATA_KEY);
    return data ? JSON.parse(data) : null;
  } catch (err) {
    logger.error('Failed to retrieve token metadata', err);
    return null;
  }
}

/**
 * Check if token exists
 * @returns {boolean} Token is available
 */
export function hasToken() {
  return !!getToken();
}

/**
 * Check if token might be expired
 * (Simple heuristic - actual validation happens at API level)
 * @returns {boolean} Token may be expired
 */
export function isTokenExpired() {
  const metadata = getTokenMetadata();
  if (!metadata || !metadata.expiresAt) return false;
  
  const expiryTime = new Date(metadata.expiresAt).getTime();
  const now = new Date().getTime();
  return now >= expiryTime;
}

/**
 * Get token age in seconds
 * @returns {number} Seconds since token was stored
 */
export function getTokenAge() {
  const metadata = getTokenMetadata();
  if (!metadata || !metadata.storedAt) return 0;
  
  const storedTime = new Date(metadata.storedAt).getTime();
  const now = new Date().getTime();
  return Math.floor((now - storedTime) / 1000);
}

/**
 * Clear authentication token
 * @returns {boolean} Success
 */
export function clearToken() {
  return setToken(null);
}

/**
 * Store phone number associated with token
 * @param {string} phone - Phone number
 * @returns {boolean} Success
 */
export function setPhone(phone) {
  try {
    if (!phone) {
      localStorage.removeItem(config.storage.phoneKey);
      return true;
    }
    localStorage.setItem(config.storage.phoneKey, phone);
    return true;
  } catch (err) {
    logger.error('Failed to store phone', err);
    return false;
  }
}

/**
 * Retrieve phone number
 * @returns {string|null} Phone number or null
 */
export function getPhone() {
  try {
    return localStorage.getItem(config.storage.phoneKey);
  } catch (err) {
    logger.error('Failed to retrieve phone', err);
    return null;
  }
}

/**
 * Clear phone number
 * @returns {boolean} Success
 */
export function clearPhone() {
  return setPhone(null);
}

/**
 * Clear all authentication data
 * @returns {boolean} Success
 */
export function clearAuth() {
  return clearToken() && clearPhone();
}

export default {
  setToken,
  getToken,
  getTokenMetadata,
  hasToken,
  isTokenExpired,
  getTokenAge,
  clearToken,
  setPhone,
  getPhone,
  clearPhone,
  clearAuth,
};
