import { useState, useEffect } from 'react';

const CACHE_KEY = 'kin_version_cache';
const CACHE_TTL = 5 * 60 * 1000; // 5 minutes

export function useVersionCheck() {
  const [updateData, setUpdateData] = useState(null);
  const [showDialog, setShowDialog] = useState(false);

  useEffect(() => {
    const check = async () => {
      // Check cache first
      const cached = localStorage.getItem(CACHE_KEY);
      if (cached) {
        const { data, timestamp } = JSON.parse(cached);
        if (Date.now() - timestamp < CACHE_TTL) {
          if (data.update_available) {
            setUpdateData(data);
            setShowDialog(true);
          }
          return;
        }
      }

      // Fetch fresh
      try {
        const res = await fetch('/api/v1/version?current=1&platform=android');
        const data = await res.json();
        
        // Cache response
        localStorage.setItem(CACHE_KEY, JSON.stringify({
          data,
          timestamp: Date.now(),
        }));

        if (data.update_available) {
          setUpdateData(data);
          setShowDialog(true);
        }
      } catch (e) {
        // Offline — use cached if available
        if (cached) {
          const { data } = JSON.parse(cached);
          if (data.update_available) {
            setUpdateData(data);
            setShowDialog(true);
          }
        }
      }
    };
    check();
  }, []);

  const handleDismiss = () => setShowDialog(false);

  return { showDialog, updateData, handleDismiss };
}
