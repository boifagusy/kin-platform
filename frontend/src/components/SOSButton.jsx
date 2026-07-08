// KIN OS — SOSButton
// Status: Production Foundation
// Purpose: Reusable SOS button
// Single Responsibility: Presentation only

import React, { useState } from 'react';
import { useSOS } from '../hooks/useSOS';
import SOSBlockedPopup from './dashboard/SOSBlockedPopup';

const SOSButton = ({
  variant = 'floating',
  size = 'md',
  className = '',
  onSOSStart = null,
  onSOSComplete = null,
  onBlocked = null,
  ...props
}) => {
  const { canTriggerSOS, triggerSOS, isSOSTriggered } = useSOS();
  const [showPopup, setShowPopup] = useState(false);
  const [blockReason, setBlockReason] = useState(null);

  const sizeClasses = {
    sm: 'w-10 h-10 text-sm',
    md: 'w-14 h-14 text-base',
    lg: 'w-16 h-16 text-lg',
  };

  const variantClasses = {
    floating: 'fixed bottom-24 right-4 z-50 shadow-lg shadow-red-500/30',
    inline: 'relative',
    icon: 'relative',
  };

  const handlePress = async () => {
    const status = canTriggerSOS();

    if (!status.can) {
      setBlockReason(status.reason);
      setShowPopup(true);
      if (onBlocked) {
        onBlocked(status);
      }
      return;
    }

    if (onSOSStart) {
      onSOSStart();
    }

    const result = await triggerSOS();

    if (onSOSComplete) {
      onSOSComplete(result);
    }
  };

  // Floating button with SOS text
  if (variant === 'floating') {
    return (
      <>
        <button
          onClick={handlePress}
          disabled={isSOSTriggered}
          className={`relative -mt-6 w-14 h-14 rounded-full bg-gradient-to-br from-[#DC3545] to-[#b02a37] shadow-lg shadow-red-500/30 flex items-center justify-center active:scale-90 transition-transform ${className}`}
          aria-label="SOS Emergency"
          {...props}
        >
          <span className="text-2xl font-bold text-white">SOS</span>
        </button>

        <SOSBlockedPopup
          isOpen={showPopup}
          onClose={() => setShowPopup(false)}
          reason={blockReason}
        />
      </>
    );
  }

  // Inline or icon button
  return (
    <>
      <button
        onClick={handlePress}
        disabled={isSOSTriggered}
        className={`flex items-center justify-center rounded-full bg-gradient-to-br from-[#DC3545] to-[#b02a37] shadow-lg shadow-red-500/30 active:scale-90 transition-transform ${sizeClasses[size]} ${variantClasses[variant]} ${className}`}
        aria-label="SOS Emergency"
        {...props}
      >
        {variant === 'icon' ? (
          <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        ) : (
          <span className="text-lg font-bold text-white">SOS</span>
        )}
      </button>

      <SOSBlockedPopup
        isOpen={showPopup}
        onClose={() => setShowPopup(false)}
        reason={blockReason}
      />
    </>
  );
};

export default SOSButton;
