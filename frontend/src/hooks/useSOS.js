// KIN OS — useSOS Hook
// Status: Production Foundation
// Purpose: Orchestrate SOS state using existing services

import { useState, useEffect, useCallback } from 'react';
import safetyService from '../services/safetyService';
import sosService from '../services/sosService';
import trustedContactService from '../services/trustedContactService';

export const useSOS = () => {
  const [hasVerifiedContact, setHasVerifiedContact] = useState(false);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [isSOSTriggered, setIsSOSTriggered] = useState(false);
  const [phone, setPhone] = useState('');

  // Load trusted contact status
  useEffect(() => {
    const loadStatus = async () => {
      try {
        setLoading(true);
        const status = await trustedContactService.loadStatus();
        setHasVerifiedContact(status.verifiedContactExists);
        const phoneFromStorage = localStorage.getItem('kin_phone');
        setPhone(phoneFromStorage || '');
        setError(null);
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    loadStatus();

    const unsubscribe = trustedContactService.subscribe((state) => {
      setHasVerifiedContact(state.verifiedContactExists);
    });

    return () => unsubscribe();
  }, []);

  // Check if SOS can be triggered
  const canTriggerSOS = useCallback(() => {
    // Check safety monitoring via safetyService
    if (!safetyService.isMonitoringEnabled()) {
      return {
        can: false,
        reason: 'Safety Monitoring is disabled.',
        action: 'Enable it from Settings → Safety.',
      };
    }

    // Check trusted contact
    if (!hasVerifiedContact) {
      return {
        can: false,
        reason: 'You need at least one verified trusted contact to use SOS.',
        action: 'Add a Trusted Contact from the Network page.',
      };
    }

    return {
      can: true,
      reason: null,
      action: null,
    };
  }, [hasVerifiedContact]);

  // Trigger SOS
  const triggerSOS = useCallback(async (locationData = null) => {
    const status = canTriggerSOS();
    if (!status.can) {
      return { success: false, error: status.reason };
    }

    setIsSOSTriggered(true);
    setError(null);

    try {
      const result = await sosService.triggerSOS({
        silent: false,
        location: locationData,
        phone: phone,
      });
      setIsSOSTriggered(false);
      return { success: true, data: result };
    } catch (error) {
      setIsSOSTriggered(false);
      setError(error.message);
      return { success: false, error: error.message };
    }
  }, [phone, canTriggerSOS]);

  // Silent SOS
  const triggerSilentSOS = useCallback(async (locationData = null) => {
    const status = canTriggerSOS();
    if (!status.can) {
      return { success: false, error: status.reason };
    }

    setIsSOSTriggered(true);
    setError(null);

    try {
      const result = await sosService.triggerSOS({
        silent: true,
        location: locationData,
        phone: phone,
      });
      setIsSOSTriggered(false);
      return { success: true, data: result };
    } catch (error) {
      setIsSOSTriggered(false);
      setError(error.message);
      return { success: false, error: error.message };
    }
  }, [phone, canTriggerSOS]);

  // Get block reason
  const getBlockReason = useCallback(() => {
    return canTriggerSOS();
  }, [canTriggerSOS]);

  // Reset state
  const resetSOS = useCallback(() => {
    setIsSOSTriggered(false);
    setError(null);
  }, []);

  return {
    hasVerifiedContact,
    loading,
    error,
    isSOSTriggered,
    phone,
    canTriggerSOS,
    triggerSOS,
    triggerSilentSOS,
    getBlockReason,
    resetSOS,
  };
};

export default useSOS;
