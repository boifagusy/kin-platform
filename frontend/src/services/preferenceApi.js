// KIN OS — USER PREFERENCES API CLIENT
// Status: Production Foundation
// Purpose: API client for user preferences

import { getToken } from '../utils/auth';

const API_BASE = import.meta.env.VITE_API_URL || '/api/v1';

export const preferenceApi = {
  // Get all preferences
  getAll: async () => {
    const response = await fetch(`${API_BASE}/user/preferences`, {
      headers: {
        'Authorization': `Bearer ${getToken()}`,
        'Accept': 'application/json',
      },
    });
    if (!response.ok) throw new Error('Failed to fetch preferences');
    return response.json();
  },

  // Get preferences by category
  getCategory: async (category) => {
    const response = await fetch(`${API_BASE}/user/preferences/category/${category}`, {
      headers: {
        'Authorization': `Bearer ${getToken()}`,
        'Accept': 'application/json',
      },
    });
    if (!response.ok) throw new Error(`Failed to fetch ${category} preferences`);
    return response.json();
  },

  // Update preferences
  update: async (category, preferences) => {
    const response = await fetch(`${API_BASE}/user/preferences`, {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${getToken()}`,
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
    const response = await fetch(`${API_BASE}/user/preferences/${category}/${key}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${getToken()}`,
        'Accept': 'application/json',
      },
    });
    if (!response.ok) throw new Error('Failed to reset preference');
    return response.json();
  },
};
