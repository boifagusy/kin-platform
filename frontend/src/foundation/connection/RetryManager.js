// KIN Platform — RetryManager
// Exponential backoff retry logic

class RetryManager {
    constructor(config = {}) {
        this.maxAttempts = config.maxAttempts || 3;
        this.baseDelay = config.baseDelay || 2000;
        this.maxDelay = config.maxDelay || 30000;
        this.shouldRetryFn = config.shouldRetry || this.defaultShouldRetry;
    }

    // Execute with retry
    async execute(fn, context = {}) {
        let lastError = null;
        let attempt = 0;

        while (attempt < this.maxAttempts) {
            attempt++;
            try {
                return await fn();
            } catch (error) {
                lastError = error;
                const shouldRetry = this.shouldRetryFn(error, attempt, context);
                
                if (!shouldRetry || attempt >= this.maxAttempts) {
                    break;
                }

                const delay = this.calculateDelay(attempt);
                await this.wait(delay);
            }
        }

        // All attempts failed
        throw lastError;
    }

    // Calculate exponential backoff delay
    calculateDelay(attempt) {
        // Exponential backoff: 2^attempt * baseDelay
        const delay = Math.pow(2, attempt) * this.baseDelay;
        // Cap at maxDelay
        return Math.min(delay, this.maxDelay);
    }

    // Default retry condition
    defaultShouldRetry(error, attempt, context) {
        // Don't retry if error is not retryable
        if (error.isRetryable === false) {
            return false;
        }

        // Don't retry if error has a status and it's not retryable
        if (error.status && !this.isRetryableStatus(error.status)) {
            return false;
        }

        // Network errors (status 0) are retryable
        if (error.status === 0) {
            return true;
        }

        // Server errors (5xx) are retryable
        if (error.status && error.status >= 500) {
            return true;
        }

        // Rate limiting is retryable
        if (error.status === 429) {
            return true;
        }

        // Timeout errors are retryable
        if (error.name === 'AbortError' || error.type === 'timeout') {
            return true;
        }

        return false;
    }

    // Check if status code is retryable
    isRetryableStatus(status) {
        // Network error
        if (status === 0) return true;
        // 5xx Server errors
        if (status >= 500) return true;
        // 429 Rate limit
        if (status === 429) return true;
        // 408 Request timeout
        if (status === 408) return true;
        // 503 Service unavailable
        if (status === 503) return true;
        // 504 Gateway timeout
        if (status === 504) return true;
        return false;
    }

    // Set retry condition function
    setShouldRetry(fn) {
        if (typeof fn === 'function') {
            this.shouldRetryFn = fn;
        }
    }

    // Wait helper
    wait(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

export default RetryManager;
