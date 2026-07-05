// KIN Platform — EnvironmentManager
// Automatic environment detection

class EnvironmentManager {
    constructor() {
        this.environment = null;
        this.config = null;
        this.detect();
    }

    detect() {
        const env = this.detectEnvironment();
        this.environment = env.name;
        this.config = env;
        return env;
    }

    detectEnvironment() {
        // Check for Android
        const isAndroid = window.navigator.userAgent.includes('Android');
        const isEmulator = window.navigator.userAgent.includes('Emulator') || 
                          window.navigator.userAgent.includes('Android Studio');

        // Check for development
        const isDev = import.meta.env.DEV;
        const isProd = import.meta.env.PROD;

        // Check for local network
        const hostname = window.location.hostname;
        const isLocalNetwork = hostname.match(/^(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1]))/);

        // Environment detection order
        if (isProd) {
            return {
                name: 'production',
                url: import.meta.env.VITE_API_URL || 'https://api.kin.app',
                apiPrefix: '',
                description: 'Production Environment'
            };
        }

        if (isAndroid && isEmulator) {
            return {
                name: 'android_emulator',
                url: 'http://10.0.2.2:8000',
                apiPrefix: '/api',
                description: 'Android Emulator'
            };
        }

        if (isAndroid) {
            const host = window.location.hostname;
            return {
                name: 'android_device',
                url: `http://${host}:8000`,
                apiPrefix: '/api',
                description: 'Android Physical Device'
            };
        }

        if (isLocalNetwork) {
            return {
                name: 'local_network',
                url: `http://${hostname}:8000`,
                apiPrefix: '/api',
                description: 'Local Network'
            };
        }

        if (isDev) {
            return {
                name: 'development',
                url: 'http://127.0.0.1:8000',
                apiPrefix: '/api',
                description: 'Development Environment'
            };
        }

        // Fallback
        return {
            name: 'unknown',
            url: 'http://127.0.0.1:8000',
            apiPrefix: '/api',
            description: 'Unknown Environment (using fallback)'
        };
    }

    getEnvironment() {
        return this.environment;
    }

    getConfig() {
        return this.config;
    }

    getApiUrl() {
        return this.config.url + this.config.apiPrefix;
    }

    getBaseUrl() {
        return this.config.url;
    }

    isDevelopment() {
        return this.environment === 'development';
    }

    isProduction() {
        return this.environment === 'production';
    }

    isAndroid() {
        return this.environment === 'android_emulator' || this.environment === 'android_device';
    }

    isLocalNetwork() {
        return this.environment === 'local_network';
    }

    getDescription() {
        return this.config.description;
    }
}

// Singleton instance
const environmentManager = new EnvironmentManager();
export default environmentManager;
