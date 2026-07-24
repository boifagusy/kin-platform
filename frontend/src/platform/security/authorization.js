/**
 * Platform Security Authorization
 * 
 * Authorization helpers and permission checking.
 * Provides utilities for protecting features and checking user permissions.
 */

import { logger } from '../core/logger';
import { getToken } from './token';

/**
 * Check if user has authorization (token exists)
 * @returns {boolean} User is authorized
 */
export function isAuthorized() {
  return !!getToken();
}

/**
 * Require authorization
 * Throws error if not authorized
 * @throws {Error} If not authorized
 */
export function requireAuthorization() {
  if (!isAuthorized()) {
    logger.warn('Authorization required but not present');
    throw new Error('Unauthorized: Token required');
  }
}

/**
 * Get authorization header
 * Returns Bearer token in header format
 * @returns {string|null} Authorization header value or null
 */
export function getAuthorizationHeader() {
  const token = getToken();
  if (!token) return null;
  return `Bearer ${token}`;
}

/**
 * Check if request is authorized
 * Validates Authorization header presence
 * @param {Object} headers - Request headers
 * @returns {boolean} Authorization header is present
 */
export function isRequestAuthorized(headers) {
  return !!(headers && headers['Authorization']);
}

/**
 * Require feature authorization
 * Guards access to specific features
 * @param {string} featureName - Name of feature to guard
 * @throws {Error} If not authorized
 */
export function requireFeatureAuthorization(featureName) {
  requireAuthorization();
  logger.debug(`Feature ${featureName} authorized`);
}

export default {
  isAuthorized,
  requireAuthorization,
  getAuthorizationHeader,
  isRequestAuthorized,
  requireFeatureAuthorization,
};
