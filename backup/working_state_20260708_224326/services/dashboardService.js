// KIN OS — Dashboard Service
// Status: Production Foundation
// Purpose: Single source for Dashboard Safety Status

const API_BASE = import.meta.env.VITE_API_URL || '/api/v1';

class DashboardService {
  constructor() {
    this.safetyStatus = null;
    this.listeners = [];
  }

  // Get Dashboard Safety Status
  async getSafetyStatus() {
    const token = localStorage.getItem('kin_token');
    if (!token) {
      return null;
    }

    try {
      const response = await fetch(`${API_BASE}/dashboard`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });

      if (!response.ok) {
        throw new Error('Failed to fetch dashboard');
      }

      const data = await response.json();
      
      if (data.success && data.data) {
        this.safetyStatus = {
          hasVerifiedContact: data.data.has_verified_contact || false,
          contacts: data.data.trusted_contacts || [],
          verifiedContact: data.data.trusted_contact || null,
          safetyScore: data.data.safety_score || 0,
          pendingTasks: data.data.pending_tasks || [],
        };
        
        this.notifyListeners();
        return this.safetyStatus;
      }

      return null;
    } catch (error) {
      console.error('DashboardService error:', error);
      return null;
    }
  }

  // Get current safety status
  getSafetyStatusSync() {
    return this.safetyStatus;
  }

  // Check if user has verified contact
  hasVerifiedContact() {
    return this.safetyStatus?.hasVerifiedContact || false;
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
    for (const callback of this.listeners) {
      try {
        callback(this.safetyStatus);
      } catch (error) {
        console.error('DashboardService subscriber error:', error);
      }
    }
  }
}

// Singleton
const dashboardService = new DashboardService();
export default dashboardService;
