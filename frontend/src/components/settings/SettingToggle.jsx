// KIN OS — Setting Toggle
// Reusable toggle component for settings screens
// Status: Production Foundation

import React from 'react';

const SettingToggle = ({ 
  label, 
  description, 
  value, 
  onChange, 
  disabled = false,
  loading = false 
}) => {
  return (
    <div className="flex items-center justify-between py-4 border-b border-[#E9ECEF] last:border-0">
      <div className="flex-1 pr-4">
        <p className="text-sm font-medium text-[#1A1A1A]">{label}</p>
        {description && (
          <p className="text-xs text-[#6C757D] mt-0.5">{description}</p>
        )}
      </div>
      <button
        onClick={() => !disabled && !loading && onChange(!value)}
        disabled={disabled || loading}
        className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[#1A5632] focus:ring-offset-2 ${
          value ? 'bg-[#1A5632]' : 'bg-[#CED4DA]'
        } ${disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'}`}
        role="switch"
        aria-checked={value}
        aria-label={label}
      >
        <span
          className={`inline-block h-5 w-5 transform rounded-full bg-white shadow-md transition-transform duration-200 ${
            value ? 'translate-x-6' : 'translate-x-0.5'
          }`}
        />
        {loading && (
          <span className="absolute inset-0 flex items-center justify-center">
            <span className="h-3 w-3 rounded-full border-2 border-white border-t-transparent animate-spin" />
          </span>
        )}
      </button>
    </div>
  );
};

export default SettingToggle;
