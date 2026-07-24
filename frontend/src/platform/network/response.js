/**
 * Platform Network Response
 * 
 * Parses and validates HTTP responses consistently.
 * Handles errors, redirects, and content negotiation.
 */

import { logger } from '../core/logger';
import { HTTP_STATUS } from '../core/constants';
import { NetworkError, AuthenticationError, AuthorizationError } from '../core/errors';

/**
 * Parse response body
 * Handles JSON, text, and binary content
 * @param {Response} response - Fetch response object
 * @returns {Promise<any>} Parsed response body
 */
export async function parseBody(response) {
  const contentType = response.headers.get('content-type');

  if (!contentType) {
    return null;
  }

  if (contentType.includes('application/json')) {
    return response.json();
  }

  if (contentType.includes('text/')) {
    return response.text();
  }

  // Binary content (blob)
  return response.blob();
}

/**
 * Validate HTTP response status
 * Throws appropriate errors for failures
 * @param {Response} response - Fetch response object
 * @throws {AuthenticationError} 401 Unauthorized
 * @throws {AuthorizationError} 403 Forbidden
 * @throws {NetworkError} Other non-2xx status
 */
export function validateStatus(response) {
  if (response.ok) {
    return; // 2xx is acceptable
  }

  const { status } = response;

  if (status === HTTP_STATUS.UNAUTHORIZED) {
    throw new AuthenticationError('Unauthenticated.');
  }

  if (status === HTTP_STATUS.FORBIDDEN) {
    throw new AuthorizationError('Insufficient permissions.');
  }

  throw new NetworkError(`HTTP ${status}`, status);
}

/**
 * Handle response
 * Parses body, validates status, returns data
 * @param {Response} response - Fetch response object
 * @returns {Promise<Object>} Parsed response data
 */
export async function handleResponse(response) {
  const body = await parseBody(response);

  try {
    validateStatus(response);
  } catch (err) {
    logger.error(`Response error: ${response.status}`, { url: response.url, status: response.status });
    throw err;
  }

  return body;
}

export default {
  parseBody,
  validateStatus,
  handleResponse,
};
