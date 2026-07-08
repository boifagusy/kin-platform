// KIN OS — Safety Service
// Status: Production Foundation
// Purpose: Single source of truth for all safety decisions

import React from 'react';
import { useUserPreference } from '../hooks/useUserPreference';

class SafetyService {
  constructor() {
    this.preferences = null;
    this.subscribers = [];
  }

  // Initialize with preferences
  init(preferences) {
    this.preferences = preferences;
    this.notifySubscribers();
  }

  // Subscribe to preference changes
  subscribe(callback) {
    this.subscribers.push(callback);
    return () => {
      this.subscribers = this.subscribers.filter(cb => cb !== callback);
    };
  }

  notifySubscribers() {
    const state = this.getState();
    for (const callback of this.subscribers) {
      try {
        callback(state);
      } catch (error) {
        console.error('SafetyService subscriber error:', error);
      }
    }
  }

  // Get current safety state
  getState() {
    const prefs = this.preferences?.safety || {};
    return {
      monitoring: prefs.monitoring ?? true,
      locationTracking: prefs.location_tracking ?? true,
      backgroundService: prefs.background_service ?? true,
      sosPowerButton: prefs.sos_power_button ?? false,
      batteryOptimization: prefs.battery_optimization ?? false,
    };
  }

  // ─── Safety Decisions ──────────────────────────────────────────────

  isMonitoringEnabled() {
    return this.preferences?.safety?.monitoring ?? true;
  }

  canTriggerSOS() {
    // SOS requires monitoring to be enabled
    return this.isMonitoringEnabled();
  }

  isSilentSOS() {
    return this.preferences?.safety?.silent_sos ?? false;
  }

  isPowerButtonEnabled() {
    return this.preferences?.safety?.sos_power_button ?? false;
  }

  isLocationTrackingEnabled() {
    return this.preferences?.safety?.location_tracking ?? true;
  }

  isBackgroundServiceEnabled() {
    return this.preferences?.safety?.background_service ?? true;
  }

  isBatteryOptimizationDisabled() {
    return this.preferences?.safety?.battery_optimization ?? false;
  }

  // ─── SOS Block Reasons ────────────────────────────────────────────

  getSOSBlockReason() {
    if (!this.isMonitoringEnabled()) {
      return {
        blocked: true,
        reason: 'Safety Monitoring is disabled.',
        action: 'Enable it from Settings → Safety.',
      };
    }
    return {
      blocked: false,
      reason: null,
      action: null,
    };
  }

  // ─── Power Button SOS ─────────────────────────────────────────────

  isPowerButtonSOSAvailable() {
    return this.isMonitoringEnabled() && this.isPowerButtonEnabled();
  }

  // ─── Silent SOS ──────────────────────────────────────────────────

  shouldSendSilentSOS() {
    return this.isMonitoringEnabled() && this.isSilentSOS();
  }
}

// Create singleton
let safetyServiceInstance = null;

export const getSafetyService = () => {
  if (!safetyServiceInstance) {
    safetyServiceInstance = new SafetyService();
  }
  return safetyServiceInstance;
};

// React hook for components
export const useSafetyService = () => {
  const { preferences } = useUserPreference();
  const service = getSafetyService();

  // Sync preferences with service
  React.useEffect(() => {
    if (preferences) {
      service.init(preferences);
    }
  }, [preferences]);

  return service;
};

// Export the singleton for non-React usage
export const safetyService = getSafetyService();

export default safetyService;
