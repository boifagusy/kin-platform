/**
 * Platform Network Retry
 * 
 * Implements exponential backoff retry logic for transient failures.
 * Does not retry on client errors (4xx) or permanent failures.
 */

import { logger } from '../core/logger';
import { HTTP_STATUS } from '../core/constants';

/**
 * Retry configuration
 */
export const retryConfig = {
  maxAttempts: 3,
  initialDelay: 1000, // 1 second
  maxDelay: 10000, // 10 seconds
  backoffMultiplier: 2,
  retryableStatuses: [
    HTTP_STATUS.SERVER_ERROR, // 500
    HTTP_STATUS.SERVICE_UNAVAILABLE, // 503
    408, // Request Timeout
    429, // Too Many Requests
  ],
};

/**
 * Check if error is retryable
 * @param {Error} error - Error to check
 * @param {number} statusCode - HTTP status code
 * @returns {boolean} Whether error is retryable
 */
export function isRetryable(error, statusCode) {
  // Network errors (no status code) are retryable
  if (!statusCode) {
    return !(error.name === 'AuthenticationError' || error.name === 'AuthorizationError');
  }

  // Only retry on server errors (5xx) and specific status codes
  return retryConfig.retryableStatuses.includes(statusCode);
}

/**
 * Calculate delay for retry attempt
 * Uses exponential backoff with jitter
 * @param {number} attempt - Attempt number (0-indexed)
 * @returns {number} Delay in milliseconds
 */
export function calculateDelay(attempt) {
  const exponentialDelay = retryConfig.initialDelay * Math.pow(retryConfig.backoffMultiplier, attempt);
  const cappedDelay = Math.min(exponentialDelay, retryConfig.maxDelay);
  const jitter = Math.random() * 0.1 * cappedDelay; // 10% jitter
  return cappedDelay + jitter;
}

/**
 * Retry a request with exponential backoff
 * @param {Function} fn - Function that returns a Promise
 * @param {Object} options - Retry options
 * @returns {Promise} Result of successful attempt
 */
export async function retry(fn, options = {}) {
  const maxAttempts = options.maxAttempts || retryConfig.maxAttempts;
  let lastError = null;

  for (let attempt = 0; attempt < maxAttempts; attempt++) {
    try {
      logger.debug(`Attempt ${attempt + 1}/${maxAttempts}`);
      return await fn();
    } catch (err) {
      lastError = err;
      const isLastAttempt = attempt === maxAttempts - 1;

      if (isLastAttempt) {
        logger.error(`Failed after ${maxAttempts} attempts`, err);
        throw err;
      }

      const delay = calculateDelay(attempt);
      logger.warn(`Retry attempt ${attempt + 1} failed, waiting ${Math.round(delay)}ms before retry`, err);
      await new Promise(resolve => setTimeout(resolve, delay));
    }
  }

  throw lastError;
}

export default {
  retryConfig,
  isRetryable,
  calculateDelay,
  retry,
};
