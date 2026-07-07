// KIN OS — USER PREFERENCES CONTEXT
// Status: Production Foundation
// Purpose: Single source of truth for user preferences in React

import React, { createContext, useContext, useState, useEffect } from 'react';
import { preferenceApi } from '../services/preferenceApi';

const UserPreferenceContext = createContext(null);

export const UserPreferenceProvider = ({ children }) => {
  const [preferences, setPreferences] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // Load preferences on mount
  useEffect(() => {
    loadPreferences();
  }, []);

  const loadPreferences = async () => {
    setLoading(true);
    setError(null);
    try {
      const response = await preferenceApi.getAll();
      if (response.success) {
        setPreferences(response.data);
      } else {
        setError('Failed to load preferences');
      }
    } catch (err) {
      setError(err.message || 'Failed to load preferences');
    } finally {
      setLoading(false);
    }
  };

  const getCategory = (category) => {
    return preferences?.[category] || {};
  };

  const get = (category, key) => {
    return preferences?.[category]?.[key];
  };

  const update = async (category, updates) => {
    try {
      const response = await preferenceApi.update(category, updates);
      if (response.success) {
        setPreferences(response.data);
        return { success: true };
      }
      return { success: false, error: 'Update failed' };
    } catch (err) {
      return { success: false, error: err.message };
    }
  };

  const reset = async (category, key) => {
    try {
      const response = await preferenceApi.reset(category, key);
      if (response.success) {
        setPreferences(response.data);
        return { success: true };
      }
      return { success: false, error: 'Reset failed' };
    } catch (err) {
      return { success: false, error: err.message };
    }
  };

  const value = {
    preferences,
    loading,
    error,
    getCategory,
    get,
    update,
    reset,
    reload: loadPreferences,
  };

  return (
    <UserPreferenceContext.Provider value={value}>
      {children}
    </UserPreferenceContext.Provider>
  );
};

export const useUserPreference = () => {
  const context = useContext(UserPreferenceContext);
  if (!context) {
    throw new Error('useUserPreference must be used within UserPreferenceProvider');
  }
  return context;
};

export default UserPreferenceProvider;
