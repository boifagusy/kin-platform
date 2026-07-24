/**
 * Platform Security Token
 * 
 * Manages token storage and retrieval.
 * Provides single source of truth for authentication state.
 */

import { config } from '../core/config';
import { logger } from '../core/logger';

/**
 * Store authentication token
 * @param {string} token - Bearer token
 * @returns {boolean} Success
 */
export function setToken(token) {
  try {
    if (!token) {
      localStorage.removeItem(config.storage.tokenKey);
      logger.info('Token cleared');
      return true;
    }
    localStorage.setItem(config.storage.tokenKey, token);
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
 * Check if token exists
 * @returns {boolean} Token is available
 */
export function hasToken() {
  return !!getToken();
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
  hasToken,
  clearToken,
  setPhone,
  getPhone,
  clearPhone,
  clearAuth,
};
