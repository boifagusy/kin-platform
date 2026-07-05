// KIN Platform — FetchClient
// Single HTTP client wrapper around native fetch()

import EnvironmentManager from './EnvironmentManager';
import RetryManager from './RetryManager';

class FetchClient {
    constructor(config = {}) {
        this.baseURL = config.baseURL || EnvironmentManager.getApiUrl();
        this.timeout = config.timeout || 30000;
        this.defaultHeaders = config.headers || {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };
        this.retryConfig = config.retry || { 
            maxAttempts: 3, 
            baseDelay: 2000,
            maxDelay: 30000,
        };
        this.interceptors = { 
            request: [], 
            response: [], 
            error: [] 
        };
        this.retryManager = new RetryManager(this.retryConfig);
    }

    // Add request interceptor
    addRequestInterceptor(callback) {
        this.interceptors.request.push(callback);
    }

    // Add response interceptor
    addResponseInterceptor(callback) {
        this.interceptors.response.push(callback);
    }

    // Add error interceptor
    addErrorInterceptor(callback) {
        this.interceptors.error.push(callback);
    }

    // Main request method
    async request(url, options = {}) {
        const fullUrl = this.baseURL + url;
        let lastError = null;

        // Build request
        const requestOptions = {
            ...options,
            headers: {
                ...this.defaultHeaders,
                ...options.headers,
            },
            signal: this.createTimeoutSignal(this.timeout),
        };

        // Run request interceptors
        let modifiedOptions = requestOptions;
        for (const interceptor of this.interceptors.request) {
            modifiedOptions = interceptor(modifiedOptions);
        }

        // Execute with retry
        const result = await this.retryManager.execute(async () => {
            try {
                const response = await fetch(fullUrl, modifiedOptions);

                // Run response interceptors
                let modifiedResponse = response;
                for (const interceptor of this.interceptors.response) {
                    modifiedResponse = interceptor(modifiedResponse);
                }

                // Check if response is ok
                if (!response.ok) {
                    const error = await this.normalizeError(response);
                    throw error;
                }

                // Parse response
                const data = await response.json();
                return data;

            } catch (error) {
                // Run error interceptors
                for (const interceptor of this.interceptors.error) {
                    interceptor(error);
                }
                throw error;
            }
        });

        return result;
    }

    // HTTP methods
    async get(url, options = {}) {
        return this.request(url, { ...options, method: 'GET' });
    }

    async post(url, data, options = {}) {
        return this.request(url, {
            ...options,
            method: 'POST',
            body: JSON.stringify(data),
            headers: {
                'Content-Type': 'application/json',
                ...options.headers,
            },
        });
    }

    async put(url, data, options = {}) {
        return this.request(url, {
            ...options,
            method: 'PUT',
            body: JSON.stringify(data),
            headers: {
                'Content-Type': 'application/json',
                ...options.headers,
            },
        });
    }

    async patch(url, data, options = {}) {
        return this.request(url, {
            ...options,
            method: 'PATCH',
            body: JSON.stringify(data),
            headers: {
                'Content-Type': 'application/json',
                ...options.headers,
            },
        });
    }

    async delete(url, options = {}) {
        return this.request(url, { ...options, method: 'DELETE' });
    }

    // Create timeout signal
    createTimeoutSignal(timeout) {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), timeout);
        // Cleanup timeout on abort
        const originalAbort = controller.abort;
        controller.abort = function() {
            clearTimeout(timeoutId);
            originalAbort.call(this);
        };
        return controller.signal;
    }

    // Normalize error
    async normalizeError(response) {
        let data;
        try {
            data = await response.json();
        } catch {
            data = { message: response.statusText };
        }

        return {
            status: response.status,
            statusText: response.statusText,
            data: data,
            type: this.getErrorType(response.status),
            isRetryable: this.isRetryable(response.status),
        };
    }

    getErrorType(status) {
        if (status === 401) return 'unauthorized';
        if (status === 403) return 'forbidden';
        if (status === 404) return 'not_found';
        if (status === 429) return 'rate_limited';
        if (status >= 500) return 'server_error';
        if (status === 0) return 'network_error';
        return 'http_error';
    }

    isRetryable(status) {
        // Network errors (status 0) are retryable
        if (status === 0) return true;
        // 5xx server errors are retryable
        if (status >= 500) return true;
        // 429 rate limit is retryable
        if (status === 429) return true;
        // 401 may be retryable (with token refresh)
        if (status === 401) return true;
        // 404 is not retryable
        return false;
    }

    // Update base URL (for environment changes)
    setBaseURL(url) {
        this.baseURL = url;
    }

    // Update headers
    setHeaders(headers) {
        this.defaultHeaders = { ...this.defaultHeaders, ...headers };
    }

    // Add auth token
    setAuthToken(token) {
        this.defaultHeaders['Authorization'] = `Bearer ${token}`;
    }

    // Remove auth token
    clearAuthToken() {
        delete this.defaultHeaders['Authorization'];
    }
}

// Singleton instance
const fetchClient = new FetchClient();
export default fetchClient;
