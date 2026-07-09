import React, { useState, useEffect } from 'react';
import sosService from '../services/sosService';

const DisguisedCheckinButton = ({ onCheckIn }) => {
  const [tapCount, setTapCount] = useState(0);
  const [isActive, setIsActive] = useState(false);
  const [cooldown, setCooldown] = useState(0);
  const [lastTapTime, setLastTapTime] = useState(0);
  const [message, setMessage] = useState('');
  const [timer, setTimer] = useState(null);
  const [cooldownTimer, setCooldownTimer] = useState(null);

  const TAP_THRESHOLD = 5;
  const TAP_TIMEOUT = 3000;

  useEffect(() => {
    return () => {
      if (timer) clearTimeout(timer);
      if (cooldownTimer) clearInterval(cooldownTimer);
    };
  }, [timer, cooldownTimer]);

  const handleClick = async () => {
    if (cooldown > 0) {
      setMessage(`⏳ Cooldown: ${cooldown}s`);
      setTimeout(() => setMessage(''), 2000);
      return;
    }

    const now = Date.now();
    if (now - lastTapTime > TAP_TIMEOUT) {
      setTapCount(1);
    } else {
      setTapCount(prev => prev + 1);
    }
    setLastTapTime(now);

    const newCount = tapCount + 1;

    if (newCount >= TAP_THRESHOLD) {
      setTapCount(0);
      setMessage('🔇 Silent SOS Triggered!');
      
      try {
        const result = await sosService.triggerSOS({
          silent: true,
          location: { lat: 6.5244, lng: 3.3792 }
        });
        if (result.success) {
          setIsActive(true);
          setCooldown(10);
          setMessage('✅ SOS Sent Silently!');
        }
      } catch (error) {
        setMessage('❌ Error: ' + error.message);
      }
      
      setTimeout(() => setMessage(''), 3000);
      return;
    }

    setMessage(`👆 ${newCount}/${TAP_THRESHOLD}`);
    setTimeout(() => setMessage(''), 2000);
  };

  return (
    <div className="flex flex-col items-center gap-3">
      <button
        onClick={handleClick}
        disabled={cooldown > 0 || isActive}
        className={`
          w-64 py-4 px-8 rounded-xl text-lg font-bold text-white
          transition-all duration-300
          ${isActive 
            ? 'bg-green-500' 
            : cooldown > 0 
              ? 'bg-gray-400 cursor-not-allowed' 
              : 'bg-[#1A5632] hover:bg-[#144A2A] active:scale-95'
          }
        `}
      >
        {isActive ? '✅ SOS Sent Silently' :
         cooldown > 0 ? `⏳ Cooldown ${cooldown}s` :
         `📋 Check In (${tapCount}/${TAP_THRESHOLD})`}
      </button>

      {message && (
        <div className={`
          px-4 py-2 rounded-lg text-sm text-center min-w-[200px]
          ${message.includes('✅') ? 'bg-green-100 text-green-800' :
            message.includes('❌') ? 'bg-red-100 text-red-800' :
            'bg-blue-50 text-blue-800'}
        `}>
          {message}
        </div>
      )}

      <div className="text-xs text-gray-400 text-center">
        💡 Tap 5 times quickly to trigger Silent SOS
      </div>
    </div>
  );
};

export default DisguisedCheckinButton;
