// KIN OS — Trusted Contact Service
// Status: Production Foundation
// Purpose: State/service layer for trusted contacts

import dashboardService from './dashboardService';

class TrustedContactService {
  constructor() {
    this.verifiedContactExists = false;
    this.contacts = [];
    this.listeners = [];
    this.loading = false;
    this.error = null;
  }

  // Load trusted contact status from Dashboard Safety Status
  async loadStatus() {
    try {
      this.loading = true;
      this.error = null;

      // Use Dashboard Safety Status as source of truth
      const dashboardData = await dashboardService.getSafetyStatus();
      
      if (dashboardData) {
        this.verifiedContactExists = dashboardData.hasVerifiedContact || false;
        this.contacts = dashboardData.contacts || [];
        this.verifiedContact = dashboardData.verifiedContact || null;
      }

      this.loading = false;
      this.notifyListeners();
      return this.getState();
    } catch (error) {
      this.error = error.message;
      this.loading = false;
      this.notifyListeners();
      return this.getState();
    }
  }

  // Get current state
  getState() {
    return {
      verifiedContactExists: this.verifiedContactExists,
      contacts: this.contacts,
      verifiedContact: this.verifiedContact || null,
      loading: this.loading,
      error: this.error,
    };
  }

  // Check if user has a verified contact
  isVerifiedContactAvailable() {
    return this.verifiedContactExists;
  }

  // Get the first verified contact
  getVerifiedContact() {
    return this.verifiedContact || null;
  }

  // Subscribe to changes
  subscribe(callback) {
    this.listeners.push(callback);
    return () => {
      this.listeners = this.listeners.filter(cb => cb !== callback);
    };
  }

  // Notify all listeners
  notifyListeners() {
    const state = this.getState();
    for (const callback of this.listeners) {
      try {
        callback(state);
      } catch (error) {
        console.error('TrustedContactService subscriber error:', error);
      }
    }
  }
}

// Singleton
const trustedContactService = new TrustedContactService();
export default trustedContactService;
