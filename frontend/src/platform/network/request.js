/**
 * Platform Network Request
 * 
 * Wraps fetch() with consistent configuration, timeouts, and error handling.
 * Does not modify application behavior—just centralizes request logic.
 */

import { config } from '../core/config';
import { logger } from '../core/logger';
import { HTTP_METHODS, HTTP_STATUS } from '../core/constants';
import { buildHeaders, buildMultipartHeaders } from './headers';

/**
 * Execute HTTP request with platform defaults
 * @param {string} endpoint - API endpoint (e.g., '/dashboard')
 * @param {Object} options - Fetch options
 * @returns {Promise<Object>} Parsed response or error
 */
export async function request(endpoint, options = {}) {
  const url = `${config.api.baseUrl}${endpoint}`;
  const method = options.method || HTTP_METHODS.GET;
  
  // Determine headers based on content type
  const isMultipart = options.body instanceof FormData;
  const headers = isMultipart 
    ? buildMultipartHeaders(options.headers)
    : buildHeaders(options.headers);

  const fetchOptions = {
    method,
    headers,
    ...options,
  };

  // Remove Content-Type for multipart (browser will set it)
  if (isMultipart) {
    delete fetchOptions.headers['Content-Type'];
  }

  logger.debug(`${method} ${url}`, { headers: fetchOptions.headers });

  try {
    // Apply timeout
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), config.api.timeout);

    const response = await fetch(url, {
      ...fetchOptions,
      signal: controller.signal,
    });

    clearTimeout(timeoutId);

    logger.debug(`Response: ${response.status}`, { url, status: response.status });

    return {
      ok: response.ok,
      status: response.status,
      response,
    };
  } catch (err) {
    if (err.name === 'AbortError') {
      logger.error(`Timeout: ${method} ${url}`);
      throw new Error('Request timeout');
    }
    logger.error(`Network error: ${method} ${url}`, err);
    throw err;
  }
}

export default {
  request,
};
