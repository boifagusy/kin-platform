import { createContext, useContext, useState } from 'react';
import { request } from '../../services/api';

const VerificationContext = createContext(null);

const TEST_USERS = {
  owner: { phone: '+2348086448522', pin: '1234', name: 'Owner (Test User)' },
  contact_a: { phone: '+2348052692060', pin: '1234', name: 'Contact A (Idowu)' },
  contact_b: { phone: '+2348055586485', pin: '1234', name: 'Contact B (Seyi)' },
  stranger: { phone: '+2348122223336', pin: '1234', name: 'Stranger (Solar)' },
};

const ROLES = ['owner', 'contact_a', 'contact_b', 'stranger'];

export function VerificationProvider({ children }) {
  const [context, setContext] = useState({
    currentUser: null,
    currentRole: null,
    authToken: null,
    currentIncidentId: null,
    currentScenario: null,
    lastResponse: null,
    results: [],
  });

  const login = async (role) => {
    const user = TEST_USERS[role];
    const res = await fetch('http://localhost:8000/api/v1/auth/login-pin', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ phone: user.phone, pin: user.pin }),
    });
    const data = await res.json();
    if (data.token) {
      localStorage.setItem('kin_token', data.token);
      setContext(prev => ({ ...prev, currentUser: user, currentRole: role, authToken: data.token }));
      return true;
    }
    return false;
  };

  const logout = () => {
    localStorage.removeItem('kin_token');
    setContext(prev => ({ ...prev, currentUser: null, currentRole: null, authToken: null }));
  };

  const createScenario = async (type) => {
    const token = context.authToken || localStorage.getItem('kin_token');
    if (!token) return null;

    // Create an SOS via the API
    const res = await fetch('http://localhost:8000/api/v1/sos', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` },
      body: JSON.stringify({ phone: TEST_USERS.owner.phone, latitude: '6.5244', longitude: '3.3792', battery_level: 85 }),
    });
    const data = await res.json();
    if (data.success || data.incident_id) {
      const id = data.incident_id || data.data?.id;
      setContext(prev => ({ ...prev, currentIncidentId: id, currentScenario: type }));
      return id;
    }
    return null;
  };

  const setResponse = (response) => {
    setContext(prev => ({ ...prev, lastResponse: response }));
  };

  const addResult = (result) => {
    setContext(prev => ({ ...prev, results: [...prev.results, result] }));
  };

  const clearResults = () => {
    setContext(prev => ({ ...prev, results: [] }));
  };

  return (
    <VerificationContext.Provider value={{ context, login, logout, createScenario, setResponse, addResult, clearResults, ROLES, TEST_USERS }}>
      {children}
    </VerificationContext.Provider>
  );
}

export function useVerification() {
  return useContext(VerificationContext);
}
