// KIN Platform — FetchClient
// Single HTTP client wrapper around native fetch()

import configService from '../config/ConfigService';
import RetryManager from './RetryManager';
import EventBus, { Events } from '../events/EventBus';
import connectionMetrics from '../metrics/ConnectionMetrics';

class FetchClient {
    constructor(config = {}) {
        const backendUrl = configService.getBackendUrl();
        this.baseURL = config.baseURL || backendUrl;
        this.timeout = config.timeout || 30000;
        this.defaultHeaders = config.headers || {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };
        const retryConfig = configService.getRetryConfig();
        this.retryConfig = config.retry || {
            maxAttempts: retryConfig.maxAttempts,
            baseDelay: retryConfig.baseDelay,
            maxDelay: retryConfig.maxDelay,
        };
        this.interceptors = { request: [], response: [], error: [] };
        this.retryManager = new RetryManager(this.retryConfig);
        this.eventBus = EventBus;
        this.metrics = connectionMetrics;
    }

    addRequestInterceptor(callback) {
        this.interceptors.request.push(callback);
    }

    addResponseInterceptor(callback) {
        this.interceptors.response.push(callback);
    }

    addErrorInterceptor(callback) {
        this.interceptors.error.push(callback);
    }

    async request(url, options = {}) {
        const fullUrl = this.baseURL + url;
        const startTime = Date.now();

        const requestOptions = {
            ...options,
            headers: {
                ...this.defaultHeaders,
                ...options.headers,
            },
            signal: this.createTimeoutSignal(this.timeout),
        };

        let modifiedOptions = requestOptions;
        for (const interceptor of this.interceptors.request) {
            modifiedOptions = interceptor(modifiedOptions);
        }

        try {
            const result = await this.retryManager.execute(async () => {
                const response = await fetch(fullUrl, modifiedOptions);

                let modifiedResponse = response;
                for (const interceptor of this.interceptors.response) {
                    modifiedResponse = interceptor(modifiedResponse);
                }

                if (!response.ok) {
                    const error = await this.normalizeError(response);
                    throw error;
                }

                const data = await response.json();
                const duration = Date.now() - startTime;
                this.metrics.recordSuccess(duration);
                this.eventBus.publish(Events.METRICS_UPDATE, { duration });

                return data;
            });

            return result;
        } catch (error) {
            const duration = Date.now() - startTime;
            this.metrics.recordFailure(error);
            this.eventBus.publish(Events.METRICS_FAILURE, { 
                error: error.message,
                duration,
                url 
            });

            for (const interceptor of this.interceptors.error) {
                interceptor(error);
            }
            throw error;
        }
    }

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

    createTimeoutSignal(timeout) {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), timeout);
        const originalAbort = controller.abort;
        controller.abort = function() {
            clearTimeout(timeoutId);
            originalAbort.call(this);
        };
        return controller.signal;
    }

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
        if (status === 0) return true;
        if (status >= 500) return true;
        if (status === 429) return true;
        if (status === 401) return true;
        return false;
    }

    setBaseURL(url) {
        this.baseURL = url;
    }

    setHeaders(headers) {
        this.defaultHeaders = { ...this.defaultHeaders, ...headers };
    }

    setAuthToken(token) {
        this.defaultHeaders['Authorization'] = `Bearer ${token}`;
    }

    clearAuthToken() {
        delete this.defaultHeaders['Authorization'];
    }
}

const fetchClient = new FetchClient();
export default fetchClient;
