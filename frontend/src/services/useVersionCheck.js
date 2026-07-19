import { useState, useEffect, useCallback } from 'react';

const CACHE_KEY = 'kin_version_cache';
const CACHE_TTL = 5 * 60 * 1000; // 5 minutes
const VERSION_CODE = parseInt(import.meta.env.VITE_APP_VERSION_CODE || '1', 10);
const PLATFORM = 'android'; // Configurable per build target

export function useVersionCheck({ enforceOnStartup = false } = {}) {
  const [updateData, setUpdateData] = useState(null);
  const [showDialog, setShowDialog] = useState(false);
  const [policy, setPolicy] = useState('current'); // current|optional|required|force
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const evaluate = useCallback(async () => {
    setError(null);

    // Check cache first
    const cached = localStorage.getItem(CACHE_KEY);
    if (cached) {
      const { data, timestamp } = JSON.parse(cached);
      if (Date.now() - timestamp < CACHE_TTL) {
        applyPolicy(data);
        return;
      }
    }

    // Fetch fresh
    try {
      const res = await fetch(`/api/v1/version?current=${VERSION_CODE}&platform=${PLATFORM}`);
      const data = await res.json();

      if (data.error) {
        setError(data.error);
        setLoading(false);
        return;
      }

      // Cache response
      localStorage.setItem(CACHE_KEY, JSON.stringify({ data, timestamp: Date.now() }));
      applyPolicy(data);
    } catch (e) {
      setError('Unable to check for updates. Please check your connection.');
      // Offline — use cached if available
      if (cached) {
        const { data } = JSON.parse(cached);
        applyPolicy(data);
      }
      setLoading(false);
    }
  }, []);

  const applyPolicy = useCallback((data) => {
    const severity = data.update_severity || 'current';
    setPolicy(severity);

    if (severity === 'optional' || severity === 'required' || severity === 'force') {
      setUpdateData(data);
      setShowDialog(true);
    }

    setLoading(false);
  }, []);

  useEffect(() => {
    evaluate();
  }, [evaluate]);

  const handleDismiss = () => setShowDialog(false);

  const handleRetry = () => {
    setLoading(true);
    setError(null);
    evaluate();
  };

  return {
    showDialog,
    updateData,
    policy,
    loading,
    error,
    handleDismiss,
    handleRetry,
    evaluate,
  };
}
