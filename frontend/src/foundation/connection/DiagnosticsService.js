// KIN Platform — DiagnosticsService
// Classifies failures and collects diagnostic context

import EventBus, { Events } from '../events/EventBus';
import configService from '../config/ConfigService';
import connectionMetrics from '../metrics/ConnectionMetrics';

// Failure classifications
export const FailureType = {
    NETWORK_OFFLINE: 'NETWORK_OFFLINE',
    BACKEND_OFFLINE: 'BACKEND_OFFLINE',
    TIMEOUT: 'TIMEOUT',
    DNS_FAILURE: 'DNS_FAILURE',
    CORS_ERROR: 'CORS_ERROR',
    SSL_ERROR: 'SSL_ERROR',
    AUTH_EXPIRED: 'AUTH_EXPIRED',
    SERVER_ERROR: 'SERVER_ERROR',
    RATE_LIMIT: 'RATE_LIMIT',
    INVALID_RESPONSE: 'INVALID_RESPONSE',
    JSON_PARSE_ERROR: 'JSON_PARSE_ERROR',
    UNKNOWN: 'UNKNOWN',
};

class DiagnosticsService {
    constructor() {
        this.diagnostics = [];
        this.maxHistory = 100;
        this.eventBus = EventBus;
        this.metrics = connectionMetrics;
        this.config = configService;
        this.severityMap = {
            [FailureType.NETWORK_OFFLINE]: 'critical',
            [FailureType.BACKEND_OFFLINE]: 'critical',
            [FailureType.TIMEOUT]: 'high',
            [FailureType.DNS_FAILURE]: 'high',
            [FailureType.CORS_ERROR]: 'medium',
            [FailureType.SSL_ERROR]: 'high',
            [FailureType.AUTH_EXPIRED]: 'high',
            [FailureType.SERVER_ERROR]: 'high',
            [FailureType.RATE_LIMIT]: 'medium',
            [FailureType.INVALID_RESPONSE]: 'medium',
            [FailureType.JSON_PARSE_ERROR]: 'medium',
            [FailureType.UNKNOWN]: 'low',
        };
    }

    // Classify and collect diagnostic
    diagnose(error, context = {}) {
        const diagnostic = {
            id: this.generateId(),
            timestamp: Date.now(),
            type: this.classify(error, context),
            severity: this.getSeverity(error, context),
            error: {
                message: error.message || 'Unknown error',
                status: error.status || null,
                statusText: error.statusText || null,
                type: error.type || null,
                name: error.name || null,
            },
            context: {
                url: context.url || null,
                method: context.method || null,
                endpoint: context.endpoint || null,
                retryCount: context.retryCount || 0,
                latency: context.latency || null,
                platform: this.config.getPlatform(),
                environment: this.config.getEnvironment(),
                backendUrl: this.config.getBackendUrl(),
                apiUrl: this.config.getApiUrl(),
                isOnline: typeof navigator !== 'undefined' ? navigator.onLine : true,
                isNative: this.config.isNative(),
                isAndroid: this.config.isAndroid(),
                isIOS: this.config.isIOS(),
                isWeb: this.config.isWeb(),
                userAgent: typeof navigator !== 'undefined' ? navigator.userAgent : 'unknown',
                appVersion: this.config.get('appVersion'),
                connectionState: context.connectionState || 'unknown',
            },
            recommendation: this.getRecommendation(error, context),
        };

        // Add to history
        this.diagnostics.push(diagnostic);
        if (this.diagnostics.length > this.maxHistory) {
            this.diagnostics.shift();
        }

        // Update metrics
        this.metrics.recordFailure(error);

        // Publish event
        this.eventBus.publish('diagnostic.created', {
            diagnostic: diagnostic,
            timestamp: Date.now()
        });

        // Publish classification event
        this.eventBus.publish(`diagnostic.${diagnostic.type.toLowerCase()}`, {
            diagnostic: diagnostic,
            timestamp: Date.now()
        });

        return diagnostic;
    }

    // Classify the failure
    classify(error, context) {
        // Check for offline
        if (typeof navigator !== 'undefined' && !navigator.onLine) {
            return FailureType.NETWORK_OFFLINE;
        }

        // Check for abort/timeout
        if (error.name === 'AbortError' || error.type === 'timeout') {
            return FailureType.TIMEOUT;
        }

        // Check status codes
        if (error.status) {
            switch (error.status) {
                case 0:
                    return FailureType.BACKEND_OFFLINE;
                case 401:
                    return FailureType.AUTH_EXPIRED;
                case 403:
                    return FailureType.CORS_ERROR;
                case 429:
                    return FailureType.RATE_LIMIT;
                case 500:
                case 502:
                case 503:
                case 504:
                    return FailureType.SERVER_ERROR;
            }
        }

        // Check error message
        if (error.message) {
            const msg = error.message.toLowerCase();
            if (msg.includes('dns') || msg.includes('host')) {
                return FailureType.DNS_FAILURE;
            }
            if (msg.includes('cors') || msg.includes('cross-origin')) {
                return FailureType.CORS_ERROR;
            }
            if (msg.includes('ssl') || msg.includes('certificate')) {
                return FailureType.SSL_ERROR;
            }
            if (msg.includes('json') || msg.includes('parse')) {
                return FailureType.JSON_PARSE_ERROR;
            }
        }

        // Check response type
        if (context.responseType === 'invalid' || context.responseType === 'html') {
            return FailureType.INVALID_RESPONSE;
        }

        return FailureType.UNKNOWN;
    }

    // Get severity
    getSeverity(error, context) {
        const type = this.classify(error, context);
        return this.severityMap[type] || 'low';
    }

    // Get recommendation
    getRecommendation(error, context) {
        const type = this.classify(error, context);
        const recommendations = {
            [FailureType.NETWORK_OFFLINE]: 'Check your internet connection. The app will automatically retry when you\'re back online.',
            [FailureType.BACKEND_OFFLINE]: 'The Kin server is unreachable. Please check if the backend is running.',
            [FailureType.TIMEOUT]: 'The request took too long to complete. Try again or check your network speed.',
            [FailureType.DNS_FAILURE]: 'Unable to resolve the server address. Check your DNS settings.',
            [FailureType.CORS_ERROR]: 'Cross-origin request blocked. Check server CORS configuration.',
            [FailureType.SSL_ERROR]: 'SSL certificate error. Check server certificate configuration.',
            [FailureType.AUTH_EXPIRED]: 'Your session has expired. Please log in again.',
            [FailureType.SERVER_ERROR]: 'The server encountered an error. The team has been notified.',
            [FailureType.RATE_LIMIT]: 'Too many requests. Please wait a moment before trying again.',
            [FailureType.INVALID_RESPONSE]: 'Received an unexpected response format. The server may be misconfigured.',
            [FailureType.JSON_PARSE_ERROR]: 'Failed to parse server response. Check server response format.',
            [FailureType.UNKNOWN]: 'An unexpected error occurred. Please try again or contact support.',
        };
        return recommendations[type] || recommendations[FailureType.UNKNOWN];
    }

    // Get user-friendly message
    getUserMessage(error, context) {
        const type = this.classify(error, context);
        const messages = {
            [FailureType.NETWORK_OFFLINE]: '📡 You appear to be offline. We\'ll retry when you reconnect.',
            [FailureType.BACKEND_OFFLINE]: '🔴 Server is currently unavailable. Please try again later.',
            [FailureType.TIMEOUT]: '⏱️ The request timed out. Please check your connection.',
            [FailureType.DNS_FAILURE]: '🌐 Cannot reach server. Check your network configuration.',
            [FailureType.CORS_ERROR]: '🔒 Security policy blocked the request. Contact support.',
            [FailureType.SSL_ERROR]: '🔐 Secure connection failed. Contact support.',
            [FailureType.AUTH_EXPIRED]: '🔑 Your session has expired. Please log in again.',
            [FailureType.SERVER_ERROR]: '💥 Server error. We\'ve been notified.',
            [FailureType.RATE_LIMIT]: '⏳ Too many requests. Please wait.',
            [FailureType.INVALID_RESPONSE]: '⚠️ Unexpected server response. Contact support.',
            [FailureType.JSON_PARSE_ERROR]: '📄 Failed to parse server response.',
            [FailureType.UNKNOWN]: '❓ An unexpected error occurred.',
        };
        return messages[type] || messages[FailureType.UNKNOWN];
    }

    // Get diagnostic history
    getHistory(limit = 20) {
        return this.diagnostics.slice(-limit);
    }

    // Get diagnostics by type
    getByType(type) {
        return this.diagnostics.filter(d => d.type === type);
    }

    // Get summary statistics
    getSummary() {
        const summary = {
            total: this.diagnostics.length,
            byType: {},
            bySeverity: { critical: 0, high: 0, medium: 0, low: 0 },
            recent: this.diagnostics.slice(-10),
            connectionScore: this.calculateConnectionScore(),
        };

        for (const diagnostic of this.diagnostics) {
            summary.byType[diagnostic.type] = (summary.byType[diagnostic.type] || 0) + 1;
            summary.bySeverity[diagnostic.severity] = (summary.bySeverity[diagnostic.severity] || 0) + 1;
        }

        return summary;
    }

    // Calculate connection score (0-100)
    calculateConnectionScore() {
        const recent = this.diagnostics.slice(-20);
        if (recent.length === 0) return 100;

        let score = 100;
        for (const diagnostic of recent) {
            const severity = diagnostic.severity;
            if (severity === 'critical') score -= 15;
            else if (severity === 'high') score -= 8;
            else if (severity === 'medium') score -= 4;
            else if (severity === 'low') score -= 1;
        }

        return Math.max(0, Math.min(100, score));
    }

    // Get live connection score
    getConnectionScore() {
        return this.calculateConnectionScore();
    }

    // Generate ID
    generateId() {
        return Date.now().toString(36) + Math.random().toString(36).substring(2, 9);
    }

    // Clear history
    clearHistory() {
        this.diagnostics = [];
        this.eventBus.publish('diagnostics.cleared', { timestamp: Date.now() });
    }
}

export default DiagnosticsService;
