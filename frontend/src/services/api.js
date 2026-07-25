// frontend/src/services/api.js
// API Service Compatibility Layer
//
// This module provides backward compatibility with the existing application.
// All functions delegate to the Platform layer while preserving the public interface.

import * as platformClient from '../platform/network/client';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1';

// ============= CORE REQUEST (for backward compatibility) =============

export async function request(endpoint, options = {}) {
  try {
    const method = options.method || 'GET';
    
    switch (method.toUpperCase()) {
      case 'GET':
        return await platformClient.get(endpoint, options);
      case 'POST':
        return await platformClient.post(endpoint, options.body, options);
      case 'PUT':
        return await platformClient.put(endpoint, options.body, options);
      case 'PATCH':
        return await platformClient.patch(endpoint, options.body, options);
      case 'DELETE':
        return await platformClient.delete(endpoint, options);
      default:
        throw new Error(`Unsupported method: ${method}`);
    }
  } catch (error) {
    console.error('❌ API Error:', error.message);
    throw error;
  }
}

// ============= AUTH ENDPOINTS =============

export const checkPhone = async (phone) => {
  return platformClient.post('/auth/check-phone', { phone });
};

export const confirmPhone = async (phone, lastFourDigits) => {
  return platformClient.post('/auth/confirm-phone', { phone, last_four_digits: lastFourDigits });
};

export const createPin = async (pin, pinConfirmation) => {
  return platformClient.post('/auth/create-pin', { pin, pin_confirmation: pinConfirmation });
};

export const loginPin = async (phone, pin) => {
  return platformClient.post('/auth/login-pin', { phone, pin });
};

export const saveUserDetails = async (phone, fullName, email) => {
  return platformClient.post('/auth/user-details', { phone, full_name: fullName, email });
};

export const saveTrustedContact = async (data) => {
  return platformClient.post('/trusted-contacts', data);
};

export const completeOnboarding = async () => {
  return platformClient.post('/auth/complete-onboarding');
};

// ============= TRUSTED CONTACTS ENDPOINTS =============

export const getTrustedContacts = async () => {
  return platformClient.get('/trusted-contacts');
};

export const deleteTrustedContact = async (id) => {
  return platformClient.delete(`/trusted-contacts/${id}`);
};

// ============= CHECK-IN ENDPOINTS =============

export const sendCheckIn = async (status = 'safe', location = null) => {
  return platformClient.post('/checkin', { status, location });
};

export const getCheckInStatus = async () => {
  return platformClient.get('/checkin/status');
};

// ============= SOS / DURESS ENDPOINTS =============

export const activateDuress = async (location = null) => {
  return platformClient.post('/duress/activate', { location });
};

export const getSosHistory = async () => {
  return platformClient.get('/sos/history');
};

// ============= DASHBOARD ENDPOINTS =============

export const getDashboard = async () => {
  return platformClient.get('/dashboard');
};

export const getSafetyStats = async () => {
  return platformClient.get('/safety/stats');
};

// ============= ONBOARDING DRAFT ENDPOINTS =============

export const getOnboardingDraft = async () => {
  return platformClient.get('/onboarding/draft');
};

export const saveOnboardingDraft = async (data) => {
  return platformClient.post('/onboarding/draft', data);
};

// ============= DEFAULT EXPORT =============

export default {
  request,
  checkPhone,
  confirmPhone,
  createPin,
  loginPin,
  saveUserDetails,
  saveTrustedContact,
  completeOnboarding,
  getTrustedContacts,
  deleteTrustedContact,
  sendCheckIn,
  getCheckInStatus,
  activateDuress,
  getSosHistory,
  getDashboard,
  getSafetyStats,
  getOnboardingDraft,
  saveOnboardingDraft
};
