import React from 'react';
import { useNavigate } from 'react-router-dom';

function SettingsScreen() {
  const navigate = useNavigate();

  return (
    <div className="min-h-screen bg-[#F0F7F2] pb-20">
      <div className="bg-[#1A5632] px-5 pt-8 pb-4">
        <div className="flex items-center">
          <button onClick={() => navigate(-1)} className="text-white">← Back</button>
          <h1 className="text-white text-lg font-semibold ml-4">Settings</h1>
        </div>
      </div>

      <div className="px-5 py-4 max-w-md mx-auto space-y-3">
        <button onClick={() => navigate('/settings/check-in')}
          className="w-full bg-white rounded-2xl p-4 border border-[#E9ECEF] shadow-sm text-left">
          <p className="font-semibold text-[#1A5632]">Check-In Settings</p>
          <p className="text-xs text-[#6C757D] mt-1">Daily check-in time and grace period</p>
        </button>

        <button onClick={() => navigate('/settings/safe-zones')}
          className="w-full bg-white rounded-2xl p-4 border border-[#E9ECEF] shadow-sm text-left">
          <p className="font-semibold text-[#1A5632]">Safe Zones</p>
          <p className="text-xs text-[#6C757D] mt-1">Manage your safe locations</p>
        </button>
      </div>
    </div>
  );
}

export default SettingsScreen;
