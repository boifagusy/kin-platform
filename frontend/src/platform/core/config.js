/**
 * Platform Core Configuration
 * 
 * Centralized configuration for the Platform layer.
 * Sourced from environment variables and defaults.
 */

export const config = {
  // API Configuration
  api: {
    baseUrl: import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1',
    timeout: 30000, // 30 seconds
  },

  // Storage Configuration
  storage: {
    tokenKey: 'kin_token',
    phoneKey: 'kin_phone',
  },

  // Logging Configuration
  logging: {
    enabled: import.meta.env.MODE !== 'production',
    level: import.meta.env.MODE === 'production' ? 'error' : 'debug',
  },

  // Security Configuration
  security: {
    tokenRefreshThreshold: 5 * 60 * 1000, // 5 minutes before expiry
  },
};

export default config;
