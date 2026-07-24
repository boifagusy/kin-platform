/**
 * Platform Network Client (Enhanced)
 * 
 * High-level HTTP client with:
 * - Retry logic for transient failures
 * - Request/response interceptors
 * - Error normalization
 * - Request/response normalization
 */

import { logger } from '../core/logger';
import { request } from './request';
import { handleResponse } from './response';
import { retry, isRetryable } from './retry';
import { interceptors } from './interceptor';
import { normalizeRequest, normalizeResponse, normalizeError } from './normalize';

/**
 * Execute request with retry and interceptors
 * @param {string} endpoint - API endpoint
 * @param {Object} options - Request options
 * @returns {Promise<any>} Response data
 */
async function executeRequest(endpoint, options = {}) {
  const config = normalizeRequest({
    url: endpoint,
    ...options,
  });

  // Execute request interceptors
  const interceptedConfig = await interceptors.executeRequestInterceptors(config);

  // Execute with retry
  let response;
  try {
    const result = await retry(async () => {
      const res = await request(interceptedConfig.url, interceptedConfig);
      return res;
    });
    response = result.response;
  } catch (err) {
    const normalized = normalizeError(err, err.statusCode);
    logger.error('Request failed', normalized);
    throw err;
  }

  // Handle response and execute interceptors
  const data = await handleResponse(response);
  const normalized = normalizeResponse(response, data);
  await interceptors.executeResponseInterceptors(normalized);

  return data;
}

/**
 * Make HTTP GET request
 * @param {string} endpoint - API endpoint
 * @param {Object} options - Optional fetch options
 * @returns {Promise<any>} Response data
 */
export async function get(endpoint, options = {}) {
  logger.debug(`GET ${endpoint}`);
  return executeRequest(endpoint, {
    method: 'GET',
    ...options,
  });
}

/**
 * Make HTTP POST request
 * @param {string} endpoint - API endpoint
 * @param {any} body - Request body
 * @param {Object} options - Optional fetch options
 * @returns {Promise<any>} Response data
 */
export async function post(endpoint, body = null, options = {}) {
  logger.debug(`POST ${endpoint}`);
  return executeRequest(endpoint, {
    method: 'POST',
    body: body instanceof FormData ? body : JSON.stringify(body),
    ...options,
  });
}

/**
 * Make HTTP PUT request
 * @param {string} endpoint - API endpoint
 * @param {any} body - Request body
 * @param {Object} options - Optional fetch options
 * @returns {Promise<any>} Response data
 */
export async function put(endpoint, body = null, options = {}) {
  logger.debug(`PUT ${endpoint}`);
  return executeRequest(endpoint, {
    method: 'PUT',
    body: JSON.stringify(body),
    ...options,
  });
}

/**
 * Make HTTP PATCH request
 * @param {string} endpoint - API endpoint
 * @param {any} body - Request body
 * @param {Object} options - Optional fetch options
 * @returns {Promise<any>} Response data
 */
export async function patch(endpoint, body = null, options = {}) {
  logger.debug(`PATCH ${endpoint}`);
  return executeRequest(endpoint, {
    method: 'PATCH',
    body: JSON.stringify(body),
    ...options,
  });
}

/**
 * Make HTTP DELETE request
 * @param {string} endpoint - API endpoint
 * @param {Object} options - Optional fetch options
 * @returns {Promise<any>} Response data
 */
export async function httpDelete(endpoint, options = {}) {
  logger.debug(`DELETE ${endpoint}`);
  return executeRequest(endpoint, {
    method: 'DELETE',
    ...options,
  });
}

export default {
  get,
  post,
  put,
  patch,
  delete: httpDelete,
};
