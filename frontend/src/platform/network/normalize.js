/**
 * Platform Network Normalize
 * 
 * Normalizes requests and responses to standard format.
 * Ensures consistent envelope across all API calls.
 */

import { logger } from '../core/logger';

/**
 * Normalize request configuration
 * Ensures consistent format before sending
 * @param {Object} config - Request config
 * @returns {Object} Normalized config
 */
export function normalizeRequest(config) {
  const normalized = {
    method: config.method || 'GET',
    headers: config.headers || {},
    body: config.body || null,
    timeout: config.timeout || 30000,
    url: config.url,
  };

  logger.debug('Request normalized', { method: normalized.method, url: normalized.url });
  return normalized;
}

/**
 * Normalize response
 * Ensures consistent format regardless of content type
 * @param {Object} response - Response object
 * @param {any} data - Parsed response body
 * @returns {Object} Normalized response
 */
export function normalizeResponse(response, data) {
  const normalized = {
    ok: response.ok,
    status: response.status,
    statusText: response.statusText,
    headers: Object.fromEntries(response.headers.entries()),
    data: data,
    timestamp: new Date().toISOString(),
  };

  logger.debug('Response normalized', { status: normalized.status });
  return normalized;
}

/**
 * Normalize error
 * Ensures consistent error format
 * @param {Error} err - Error object
 * @param {number} statusCode - HTTP status code (if applicable)
 * @returns {Object} Normalized error
 */
export function normalizeError(err, statusCode = null) {
  const normalized = {
    name: err.name || 'Error',
    code: err.code || 'UNKNOWN_ERROR',
    message: err.message,
    statusCode: statusCode,
    timestamp: new Date().toISOString(),
  };

  logger.debug('Error normalized', { code: normalized.code, statusCode: normalized.statusCode });
  return normalized;
}

export default {
  normalizeRequest,
  normalizeResponse,
  normalizeError,
};
