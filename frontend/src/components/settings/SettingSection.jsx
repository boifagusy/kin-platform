// KIN OS — Setting Section
// Reusable section wrapper for settings screens
// Status: Production Foundation

import React from 'react';

const SettingSection = ({ title, description, children }) => {
  return (
    <div className="bg-white rounded-xl shadow-sm border border-[#E9ECEF] overflow-hidden">
      <div className="px-4 py-3 border-b border-[#E9ECEF]">
        <h3 className="text-sm font-semibold text-[#1A1A1A]">{title}</h3>
        {description && (
          <p className="text-xs text-[#6C757D] mt-0.5">{description}</p>
        )}
      </div>
      <div className="px-4">
        {children}
      </div>
    </div>
  );
};

export default SettingSection;
