// KIN Platform — ConfigService
// SINGLE SOURCE OF TRUTH for all configuration
// Includes platform detection (Capacitor)

import { Capacitor } from '@capacitor/core';

class ConfigService {
    constructor() {
        this.config = null;
        this.runtime = null;
        this.load();
    }

    // Load all configuration
    load() {
        const env = this.detectEnvironment();
        const platform = this.detectPlatform();
        const urls = this.getUrls(env);
        const features = this.getFeatures(env);
        const health = this.getHealthConfig();
        const retry = this.getRetryConfig();

        this.config = {
            env,
            platform,
            urls,
            features,
            health,
            retry,
            appName: import.meta.env.VITE_APP_NAME || 'KIN',
            appVersion: import.meta.env.VITE_APP_VERSION || '1.0.0',
        };

        this.runtime = {
            detectedAt: new Date().toISOString(),
            isDev: env === 'development',
            isProd: env === 'production',
            isStaging: env === 'staging',
            isAndroid: platform === 'android',
            isIOS: platform === 'ios',
            isWeb: platform === 'web',
            isCapacitor: platform === 'android' || platform === 'ios',
            isNative: platform === 'android' || platform === 'ios',
        };

        return this.config;
    }

    // Detect environment from import.meta.env
    detectEnvironment() {
        const mode = import.meta.env.MODE;
        if (mode === 'production') return 'production';
        if (mode === 'staging') return 'staging';
        if (mode === 'development') return 'development';
        return 'development';
    }

    // Detect platform using Capacitor
    detectPlatform() {
        if (Capacitor.isNativePlatform()) {
            const platform = Capacitor.getPlatform();
            if (platform === 'android') return 'android';
            if (platform === 'ios') return 'ios';
        }
        // Check user agent as fallback for non-Capacitor
        if (typeof window !== 'undefined') {
            const ua = window.navigator.userAgent;
            if (ua.includes('Android')) return 'android';
            if (ua.includes('iPhone') || ua.includes('iPad')) return 'ios';
        }
        return 'web';
    }

    // Get URLs for current environment
    getUrls(env) {
        const baseUrls = {
            development: {
                api: import.meta.env.VITE_API_URL || '/api',
                backend: import.meta.env.VITE_BACKEND_URL || 'http://127.0.0.1:8000',
                websocket: import.meta.env.VITE_WS_URL || 'ws://127.0.0.1:6001',
            },
            staging: {
                api: import.meta.env.VITE_API_URL || 'https://staging-api.kin.app/api',
                backend: import.meta.env.VITE_BACKEND_URL || 'https://staging-api.kin.app',
                websocket: import.meta.env.VITE_WS_URL || 'wss://staging-ws.kin.app',
            },
            production: {
                api: import.meta.env.VITE_API_URL || 'https://api.kin.app/api',
                backend: import.meta.env.VITE_BACKEND_URL || 'https://api.kin.app',
                websocket: import.meta.env.VITE_WS_URL || 'wss://ws.kin.app',
            },
        };

        // For development, detect if we're on local network
        if (env === 'development') {
            const platform = this.detectPlatform();
            if (platform === 'android') {
                // Android emulator uses 10.0.2.2
                baseUrls.development.backend = 'http://10.0.2.2:8000';
                baseUrls.development.api = 'http://10.0.2.2:8000/api';
            } else if (platform === 'web') {
                // Check if we're on a LAN IP
                const hostname = window.location.hostname;
                if (hostname.match(/^(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1]))/)) {
                    baseUrls.development.backend = `http://${hostname}:8000`;
                    baseUrls.development.api = `http://${hostname}:8000/api`;
                }
            }
        }

        return baseUrls[env] || baseUrls.development;
    }

    // Feature flags
    getFeatures(env) {
        const features = {
            offline_mode: true,
            auto_retry: true,
            health_monitoring: true,
            diagnostics: true,
            metrics: true,
        };

        // Disable some features in production
        if (env === 'production') {
            features.debug_mode = false;
        } else {
            features.debug_mode = true;
        }

        return features;
    }

    // Health check configuration
    getHealthConfig() {
        return {
            endpoint: '/api/health',
            interval: 30000, // 30 seconds
            timeout: 5000, // 5 seconds
            retryAttempts: 3,
            initialDelay: 2000,
        };
    }

    // Retry configuration
    getRetryConfig() {
        return {
            maxAttempts: 3,
            baseDelay: 2000,
            maxDelay: 30000,
            backoffFactor: 2,
        };
    }

    // Get full config
    getConfig() {
        return this.config;
    }

    // Get runtime info
    getRuntime() {
        return this.runtime;
    }

    // Get specific config value
    get(key) {
        const keys = key.split('.');
        let value = this.config;
        for (const k of keys) {
            if (value && value[k] !== undefined) {
                value = value[k];
            } else {
                return undefined;
            }
        }
        return value;
    }

    // Check if feature is enabled
    isFeatureEnabled(feature) {
        return this.config.features[feature] || false;
    }

    // Get API URL
    getApiUrl() {
        return this.config.urls.api;
    }

    // Get Backend URL
    getBackendUrl() {
        return this.config.urls.backend;
    }

    // Get WebSocket URL
    getWsUrl() {
        return this.config.urls.websocket;
    }

    // Check if running in Capacitor
    isNative() {
        return this.runtime.isNative;
    }

    // Check if running on Android
    isAndroid() {
        return this.runtime.isAndroid;
    }

    // Check if running on iOS
    isIOS() {
        return this.runtime.isIOS;
    }

    // Check if running in browser
    isWeb() {
        return this.runtime.isWeb;
    }

    // Get environment name
    getEnvironment() {
        return this.config.env;
    }

    // Check if development
    isDev() {
        return this.runtime.isDev;
    }

    // Check if production
    isProd() {
        return this.runtime.isProd;
    }
}

// Singleton instance
const configService = new ConfigService();
export default configService;
