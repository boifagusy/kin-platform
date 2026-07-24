/**
 * Platform Network Client
 * 
 * High-level HTTP client combining request and response handling.
 * Public interface for making API calls.
 */

import { logger } from '../core/logger';
import { request } from './request';
import { handleResponse } from './response';

/**
 * Make HTTP GET request
 * @param {string} endpoint - API endpoint
 * @param {Object} options - Optional fetch options
 * @returns {Promise<any>} Response data
 */
export async function get(endpoint, options = {}) {
  logger.debug(`GET ${endpoint}`);
  const { response } = await request(endpoint, {
    method: 'GET',
    ...options,
  });
  return handleResponse(response);
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
  const { response } = await request(endpoint, {
    method: 'POST',
    body: body instanceof FormData ? body : JSON.stringify(body),
    ...options,
  });
  return handleResponse(response);
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
  const { response } = await request(endpoint, {
    method: 'PUT',
    body: JSON.stringify(body),
    ...options,
  });
  return handleResponse(response);
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
  const { response } = await request(endpoint, {
    method: 'PATCH',
    body: JSON.stringify(body),
    ...options,
  });
  return handleResponse(response);
}

/**
 * Make HTTP DELETE request
 * @param {string} endpoint - API endpoint
 * @param {Object} options - Optional fetch options
 * @returns {Promise<any>} Response data
 */
export async function httpDelete(endpoint, options = {}) {
  logger.debug(`DELETE ${endpoint}`);
  const { response } = await request(endpoint, {
    method: 'DELETE',
    ...options,
  });
  return handleResponse(response);
}

export default {
  get,
  post,
  put,
  patch,
  delete: httpDelete,
};
