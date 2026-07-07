// KIN OS — USER PREFERENCES API CLIENT
// Status: Production Foundation
// Purpose: API client for user preferences

const API_BASE = import.meta.env.VITE_API_URL || '/api/v1';

export const preferenceApi = {
  // Get all preferences
  getAll: async () => {
    const token = localStorage.getItem('kin_token');
    const response = await fetch(`${API_BASE}/user/preferences`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    });
    if (!response.ok) throw new Error('Failed to fetch preferences');
    return response.json();
  },

  // Get preferences by category
  getCategory: async (category) => {
    const token = localStorage.getItem('kin_token');
    const response = await fetch(`${API_BASE}/user/preferences/category/${category}`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    });
    if (!response.ok) throw new Error(`Failed to fetch ${category} preferences`);
    return response.json();
  },

  // Update preferences
  update: async (category, preferences) => {
    const token = localStorage.getItem('kin_token');
    const response = await fetch(`${API_BASE}/user/preferences`, {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ category, preferences }),
    });
    if (!response.ok) throw new Error('Failed to update preferences');
    return response.json();
  },

  // Reset a preference
  reset: async (category, key) => {
    const token = localStorage.getItem('kin_token');
    const response = await fetch(`${API_BASE}/user/preferences/${category}/${key}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    });
    if (!response.ok) throw new Error('Failed to reset preference');
    return response.json();
  },
};
