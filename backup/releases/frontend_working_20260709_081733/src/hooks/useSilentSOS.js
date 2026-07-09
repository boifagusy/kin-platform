import { useState, useEffect, useCallback } from 'react';
import sosService from '../services/sosService';
import { secureStorage } from '../services/secureStorage';

export const useSilentSOS = () => {
  const [isSilent, setIsSilent] = useState(false);
  const [isActive, setIsActive] = useState(false);
  const [isProcessing, setIsProcessing] = useState(false);

  useEffect(() => {
    const active = sosService.isSilentSOSActive();
    setIsActive(active);
  }, []);

  const triggerDuress = useCallback(async (enteredPin, location = null) => {
    try {
      const storedDuressPin = await secureStorage.get('duress_pin');
      if (!storedDuressPin || enteredPin !== storedDuressPin) {
        return { success: false, message: 'Invalid PIN' };
      }

      setIsSilent(true);
      setIsProcessing(true);
      const response = await sosService.triggerSOS({
        silent: true,
        duress: true,
        location,
      });
      setIsActive(true);
      return { success: true, response };
    } catch (error) {
      return { success: false, message: error.message };
    } finally {
      setIsProcessing(false);
    }
  }, []);

  const triggerSilent = useCallback(async (location = null) => {
    try {
      if (isProcessing) {
        return { success: false, message: 'Already processing' };
      }

      const lastTrigger = localStorage.getItem('kin_sos_last_trigger');
      if (lastTrigger) {
        const elapsed = Date.now() - parseInt(lastTrigger);
        if (elapsed < 300000) {
          return { success: false, message: 'Throttled' };
        }
      }

      setIsSilent(true);
      setIsProcessing(true);
      const response = await sosService.triggerSOS({
        silent: true,
        duress: false,
        location,
      });
      setIsActive(true);
      localStorage.setItem('kin_sos_last_trigger', Date.now().toString());

      if (window.navigator && window.navigator.vibrate) {
        window.navigator.vibrate([50, 100, 50, 100, 50]);
      }

      return { success: true, response };
    } catch (error) {
      return { success: false, message: error.message };
    } finally {
      setIsProcessing(false);
    }
  }, [isProcessing]);

  const clearSOS = useCallback(() => {
    sosService.clearSilentSOS();
    setIsActive(false);
    setIsSilent(false);
  }, []);

  return {
    isSilent,
    isActive,
    isProcessing,
    triggerSilent,
    triggerDuress,
    clearSOS,
  };
};
