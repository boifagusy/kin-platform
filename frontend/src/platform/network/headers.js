/**
 * Platform Network Headers
 * 
 * Constructs HTTP headers consistently across all requests.
 * Respects token availability and content type requirements.
 */

import { CONTENT_TYPES, HEADER_NAMES, AUTH_SCHEMES } from '../core/constants';

/**
 * Get authentication token from storage
 * @returns {string|null} Bearer token or null if not available
 */
function getToken() {
  try {
    return localStorage.getItem('kin_token');
  } catch (err) {
    return null;
  }
}

/**
 * Build standard request headers
 * Includes authorization if token exists
 * @param {Object} overrides - Additional headers to merge
 * @returns {Object} Complete headers object
 */
export function buildHeaders(overrides = {}) {
  const headers = {
    [HEADER_NAMES.CONTENT_TYPE]: CONTENT_TYPES.JSON,
    [HEADER_NAMES.ACCEPT]: CONTENT_TYPES.JSON,
  };

  const token = getToken();
  if (token) {
    headers[HEADER_NAMES.AUTHORIZATION] = `${AUTH_SCHEMES.BEARER} ${token}`;
  }

  return {
    ...headers,
    ...overrides,
  };
}

/**
 * Build multipart headers (for file uploads)
 * Omits Content-Type to let browser set boundary
 * @param {Object} overrides - Additional headers to merge
 * @returns {Object} Complete headers object
 */
export function buildMultipartHeaders(overrides = {}) {
  const headers = {
    [HEADER_NAMES.ACCEPT]: CONTENT_TYPES.JSON,
  };

  const token = getToken();
  if (token) {
    headers[HEADER_NAMES.AUTHORIZATION] = `${AUTH_SCHEMES.BEARER} ${token}`;
  }

  return {
    ...headers,
    ...overrides,
  };
}

export default {
  buildHeaders,
  buildMultipartHeaders,
  getToken,
};
