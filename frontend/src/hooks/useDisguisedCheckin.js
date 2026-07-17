import { useState, useEffect, useCallback, useRef } from 'react';
import sosService from '../services/sosService';

export const useDisguisedCheckin = () => {
  const [isActive, setIsActive] = useState(false);
  const [tapCount, setTapCount] = useState(0);
  const [lastTapTime, setLastTapTime] = useState(0);
  const [cooldownUntil, setCooldownUntil] = useState(0);
  const timeoutRef = useRef(null);

  const TAP_THRESHOLD = 5;
  const TAP_TIMEOUT = 3000;
  const COOLDOWN_MINUTES = 5;

  const isInCooldown = useCallback(() => {
    return Date.now() < cooldownUntil;
  }, [cooldownUntil]);

  const getCooldownRemaining = useCallback(() => {
    if (!isInCooldown()) return 0;
    return Math.ceil((cooldownUntil - Date.now()) / 1000);
  }, [cooldownUntil, isInCooldown]);

  const resetTaps = useCallback(() => {
    setTapCount(0);
    if (timeoutRef.current) {
      clearTimeout(timeoutRef.current);
      timeoutRef.current = null;
    }
  }, []);

  const triggerDisguisedSOS = useCallback(async () => {
    if (isInCooldown()) {
      return { success: false, message: 'Cooldown active' };
    }

    try {
      const result = await sosService.triggerSOS({
        silent: true,
        duress: false,
        location: { lat: 6.5244, lng: 3.3792 }
      });

      if (result.success) {
        setIsActive(true);
        setCooldownUntil(Date.now() + COOLDOWN_MINUTES * 60 * 1000);
        localStorage.setItem('kin_disguised_checkin_triggered', JSON.stringify({
          timestamp: new Date().toISOString(),
          cooldownUntil: Date.now() + COOLDOWN_MINUTES * 60 * 1000,
        }));
      }

      return result;
    } catch (error) {
      return { success: false, message: error.message };
    }
  }, [isInCooldown]);

  const handleCheckInTap = useCallback(async () => {
    const now = Date.now();

    if (isInCooldown()) {
      return { success: false, message: 'Cooldown active' };
    }

    if (now - lastTapTime > TAP_TIMEOUT) {
      setTapCount(0);
    }

    const newCount = tapCount + 1;
    setTapCount(newCount);
    setLastTapTime(now);


    if (newCount >= TAP_THRESHOLD) {
      resetTaps();
      return await triggerDisguisedSOS();
    }

    if (timeoutRef.current) {
      clearTimeout(timeoutRef.current);
    }
    timeoutRef.current = setTimeout(() => {
      resetTaps();
    }, TAP_TIMEOUT);

    return { success: false, progress: newCount };
  }, [tapCount, lastTapTime, resetTaps, triggerDisguisedSOS, isInCooldown]);

  const resetCooldown = useCallback(() => {
    setCooldownUntil(0);
    setIsActive(false);
    localStorage.removeItem('kin_disguised_checkin_triggered');
  }, []);

  useEffect(() => {
    try {
      const saved = localStorage.getItem('kin_disguised_checkin_triggered');
      if (saved) {
        const data = JSON.parse(saved);
        if (data.cooldownUntil > Date.now()) {
          setCooldownUntil(data.cooldownUntil);
          setIsActive(true);
        } else {
          localStorage.removeItem('kin_disguised_checkin_triggered');
        }
      }
    } catch (e) {}
  }, []);

  useEffect(() => {
    return () => {
      if (timeoutRef.current) {
        clearTimeout(timeoutRef.current);
      }
    };
  }, []);

  return {
    isActive,
    tapCount,
    isInCooldown: isInCooldown(),
    cooldownRemaining: getCooldownRemaining(),
    handleCheckInTap,
    resetCooldown,
    TAP_THRESHOLD,
  };
};

export default useDisguisedCheckin;
