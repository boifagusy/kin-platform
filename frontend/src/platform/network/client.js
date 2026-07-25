import { retry } from './retry.js';

const API_BASE = process.env.REACT_APP_API_URL || 'http://127.0.0.1:8000/api/v1';

export const executeRequest = async (url, options = {}) => {
  const defaultOptions = {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
  };

  // Read token from localStorage and add to headers if present
  const token = localStorage.getItem('kin_token');
  if (token) {
    defaultOptions.headers['Authorization'] = `Bearer ${token}`;
  }

  const mergedOptions = { ...defaultOptions, ...options };

  return await retry(async () => {
    const response = await fetch(url, mergedOptions);
    
    if (!response.ok) {
      const contentType = response.headers.get('content-type');
      if (contentType && contentType.includes('application/json')) {
        const error = await response.json();
        throw new Error(error.message || error.error || `HTTP ${response.status}`);
      } else {
        throw new Error(`HTTP ${response.status}`);
      }
    }

    const contentType = response.headers.get('content-type');
    if (contentType && contentType.includes('application/json')) {
      return await response.json();
    }

    return response;
  });
};

export const platformClient = {
  get: async (endpoint, options = {}) => {
    return executeRequest(`${API_BASE}${endpoint}`, {
      ...options,
      method: 'GET',
    });
  },

  post: async (endpoint, data, options = {}) => {
    return executeRequest(`${API_BASE}${endpoint}`, {
      ...options,
      method: 'POST',
      body: JSON.stringify(data),
    });
  },

  patch: async (endpoint, data, options = {}) => {
    return executeRequest(`${API_BASE}${endpoint}`, {
      ...options,
      method: 'PATCH',
      body: JSON.stringify(data),
    });
  },

  delete: async (endpoint, options = {}) => {
    return executeRequest(`${API_BASE}${endpoint}`, {
      ...options,
      method: 'DELETE',
    });
  },
};
