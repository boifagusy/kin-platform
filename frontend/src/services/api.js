// KIN Platform — API Service (Compatibility Layer)
// All existing API calls go through this layer
// Internally uses the new Foundation FetchClient

import { fetchClient } from '../foundation';

// Logging helper (optional, for migration tracking)
const logMigration = (endpoint, method) => {
  if (import.meta.env.DEV) {
    console.log(`[API] ${method} ${endpoint} → via FetchClient`);
  }
};

// ──────────────────────────────────────────────
// User API
// ──────────────────────────────────────────────

export const userApi = {
  async getProfile() {
    logMigration('/user/profile', 'GET');
    return fetchClient.get('/user/profile');
  },

  async updateProfile(data) {
    logMigration('/user/profile', 'PUT');
    return fetchClient.put('/user/profile', data);
  },

  async getSettings() {
    logMigration('/user/settings', 'GET');
    return fetchClient.get('/user/settings');
  },

  async updateSettings(data) {
    logMigration('/user/settings', 'PUT');
    return fetchClient.put('/user/settings', data);
  },
};

// ──────────────────────────────────────────────
// Auth API
// ──────────────────────────────────────────────

export const authApi = {
  async login(credentials) {
    logMigration('/auth/login', 'POST');
    return fetchClient.post('/auth/login', credentials);
  },

  async register(data) {
    logMigration('/auth/register', 'POST');
    return fetchClient.post('/auth/register', data);
  },

  async logout() {
    logMigration('/auth/logout', 'POST');
    return fetchClient.post('/auth/logout');
  },

  async refreshToken() {
    logMigration('/auth/refresh', 'POST');
    return fetchClient.post('/auth/refresh');
  },

  async confirmPhone(data) {
    logMigration('/auth/confirm-phone', 'POST');
    return fetchClient.post('/auth/confirm-phone', data);
  },
};

// ──────────────────────────────────────────────
// Dashboard API
// ──────────────────────────────────────────────

export const dashboardApi = {
  async getStats() {
    logMigration('/dashboard/stats', 'GET');
    return fetchClient.get('/dashboard/stats');
  },

  async getActivity() {
    logMigration('/dashboard/activity', 'GET');
    return fetchClient.get('/dashboard/activity');
  },
};

// ──────────────────────────────────────────────
// Watchtower API
// ──────────────────────────────────────────────

export const watchtowerApi = {
  async getHealth() {
    logMigration('/watchtower/health', 'GET');
    return fetchClient.get('/watchtower/health');
  },

  async getMetrics() {
    logMigration('/watchtower/metrics', 'GET');
    return fetchClient.get('/watchtower/metrics');
  },

  async getSystemHealth() {
    logMigration('/watchtower/system/health', 'GET');
    return fetchClient.get('/watchtower/system/health');
  },
};

// ──────────────────────────────────────────────
// Guardian API
// ──────────────────────────────────────────────

export const guardianApi = {
  async getStatus() {
    logMigration('/guardian/status', 'GET');
    return fetchClient.get('/guardian/status');
  },

  async getAlerts() {
    logMigration('/guardian/alerts', 'GET');
    return fetchClient.get('/guardian/alerts');
  },

  async triggerAlert(data) {
    logMigration('/guardian/alert', 'POST');
    return fetchClient.post('/guardian/alert', data);
  },
};

// ──────────────────────────────────────────────
// Pulse API
// ──────────────────────────────────────────────

export const pulseApi = {
  async getHealthData() {
    logMigration('/pulse/health', 'GET');
    return fetchClient.get('/pulse/health');
  },

  async checkin(data) {
    logMigration('/pulse/checkin', 'POST');
    return fetchClient.post('/pulse/checkin', data);
  },
};

// ──────────────────────────────────────────────
// Recovery API
// ──────────────────────────────────────────────

export const recoveryApi = {
  async getStatus() {
    logMigration('/recovery/status', 'GET');
    return fetchClient.get('/recovery/status');
  },

  async getIncidents() {
    logMigration('/recovery/incidents', 'GET');
    return fetchClient.get('/recovery/incidents');
  },

  async recover(data) {
    logMigration('/recovery/recover', 'POST');
    return fetchClient.post('/recovery/recover', data);
  },
};

// ──────────────────────────────────────────────
// Sentinel API
// ──────────────────────────────────────────────

export const sentinelApi = {
  async getSecurityStatus() {
    logMigration('/sentinel/status', 'GET');
    return fetchClient.get('/sentinel/status');
  },

  async getLogs() {
    logMigration('/sentinel/logs', 'GET');
    return fetchClient.get('/sentinel/logs');
  },
};

// ──────────────────────────────────────────────
// SOS API
// ──────────────────────────────────────────────

export const sosApi = {
  async trigger(data) {
    logMigration('/sos/trigger', 'POST');
    return fetchClient.post('/sos/trigger', data);
  },
};

// ──────────────────────────────────────────────
// Check-in API
// ──────────────────────────────────────────────

export const checkinApi = {
  async store(data) {
    logMigration('/checkin/store', 'POST');
    return fetchClient.post('/checkin/store', data);
  },
};

// ──────────────────────────────────────────────
// Trusted Contacts API
// ──────────────────────────────────────────────

export const trustedContactsApi = {
  async get() {
    logMigration('/trusted-contacts', 'GET');
    return fetchClient.get('/trusted-contacts');
  },

  async store(data) {
    logMigration('/trusted-contacts', 'POST');
    return fetchClient.post('/trusted-contacts', data);
  },

  async delete(id) {
    logMigration(`/trusted-contacts/${id}`, 'DELETE');
    return fetchClient.delete(`/trusted-contacts/${id}`);
  },
};

// ──────────────────────────────────────────────
// Onboarding API
// ──────────────────────────────────────────────

export const onboardingApi = {
  async getDraft() {
    logMigration('/onboarding/draft', 'GET');
    return fetchClient.get('/onboarding/draft');
  },

  async saveDraft(data) {
    logMigration('/onboarding/draft', 'POST');
    return fetchClient.post('/onboarding/draft', data);
  },
};

// ──────────────────────────────────────────────
// Health Check API (public)
// ──────────────────────────────────────────────

export const healthApi = {
  async check() {
    return fetchClient.get('/health');
  },
};

// ──────────────────────────────────────────────
// Default export (for backward compatibility)
// ──────────────────────────────────────────────

const api = {
  user: userApi,
  auth: authApi,
  dashboard: dashboardApi,
  watchtower: watchtowerApi,
  guardian: guardianApi,
  pulse: pulseApi,
  recovery: recoveryApi,
  sentinel: sentinelApi,
  sos: sosApi,
  checkin: checkinApi,
  trustedContacts: trustedContactsApi,
  onboarding: onboardingApi,
  health: healthApi,
};

export default api;
