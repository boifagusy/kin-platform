/**
 * Platform Network Interceptor
 * 
 * Provides hooks for request and response processing.
 * Used for logging, auth refresh, error normalization.
 */

import { logger } from '../core/logger';

class InterceptorManager {
  constructor() {
    this.requestInterceptors = [];
    this.responseInterceptors = [];
  }

  /**
   * Register a request interceptor
   * @param {Function} fn - Interceptor function (receives request config)
   */
  registerRequestInterceptor(fn) {
    this.requestInterceptors.push(fn);
  }

  /**
   * Register a response interceptor
   * @param {Function} fn - Interceptor function (receives response)
   */
  registerResponseInterceptor(fn) {
    this.responseInterceptors.push(fn);
  }

  /**
   * Execute request interceptors
   * @param {Object} config - Request configuration
   * @returns {Object} Modified request configuration
   */
  async executeRequestInterceptors(config) {
    let current = config;
    for (const interceptor of this.requestInterceptors) {
      try {
        current = await interceptor(current);
      } catch (err) {
        logger.error('Request interceptor error', err);
        throw err;
      }
    }
    return current;
  }

  /**
   * Execute response interceptors
   * @param {Object} response - Response object
   * @returns {Object} Modified response
   */
  async executeResponseInterceptors(response) {
    let current = response;
    for (const interceptor of this.responseInterceptors) {
      try {
        current = await interceptor(current);
      } catch (err) {
        logger.error('Response interceptor error', err);
        throw err;
      }
    }
    return current;
  }
}

export const interceptors = new InterceptorManager();

export default {
  interceptors,
  InterceptorManager,
};
