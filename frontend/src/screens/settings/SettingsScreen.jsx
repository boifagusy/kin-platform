import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';

const API_BASE = '/api/v1';

function SettingsScreen() {
  const navigate = useNavigate();
  const [preferences, setPreferences] = useState(null);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    fetchPreferences();
  }, []);

  const fetchPreferences = async () => {
    const token = localStorage.getItem('auth_token');
    const res = await fetch(`${API_BASE}/preferences/notifications`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    const data = await res.json();
    setPreferences(data);
  };

  const toggleChannel = (channel) => {
    setPreferences(prev => ({
      ...prev,
      channels: { ...prev.channels, [channel]: !prev.channels[channel] },
    }));
  };

  const toggleCategory = (category) => {
    setPreferences(prev => ({
      ...prev,
      categories: { ...prev.categories, [category]: !prev.categories[category] },
    }));
  };

  const savePreferences = async () => {
    setSaving(true);
    const token = localStorage.getItem('auth_token');
    await fetch(`${API_BASE}/preferences/notifications`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify(preferences),
    });
    setSaving(false);
  };

  const channelLabels = { sms: 'SMS', email: 'Email', whatsapp: 'WhatsApp', push: 'Push' };
  const categoryLabels = { security: 'Security Alerts', marketing: 'Marketing', system: 'System' };

  return (
    <div className="min-h-screen bg-[#F0F7F2] pb-20">
      <div className="bg-[#1A5632] px-5 pt-8 pb-4">
        <div className="flex items-center">
          <button onClick={() => navigate(-1)} className="text-white">← Back</button>
          <h1 className="text-white text-lg font-semibold ml-4">Settings</h1>
        </div>
      </div>

      <div className="px-5 py-4 max-w-md mx-auto space-y-3">

        {preferences && (
          <div className="bg-white rounded-2xl p-4 border border-[#E9ECEF] shadow-sm">
            <h2 className="font-semibold text-[#1A5632] mb-3">Notification Channels</h2>
            {Object.keys(channelLabels).map(ch => (
              <div key={ch} className="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                <span className="text-sm text-gray-700">{channelLabels[ch]}</span>
                <button
                  onClick={() => toggleChannel(ch)}
                  className={`w-12 h-6 rounded-full transition-colors ${preferences.channels[ch] ? 'bg-[#1A5632]' : 'bg-gray-300'}`}
                >
                  <div className={`w-5 h-5 bg-white rounded-full shadow transform transition-transform ${preferences.channels[ch] ? 'translate-x-6' : 'translate-x-1'}`} />
                </button>
              </div>
            ))}
          </div>
        )}

        {preferences && (
          <div className="bg-white rounded-2xl p-4 border border-[#E9ECEF] shadow-sm">
            <h2 className="font-semibold text-[#1A5632] mb-3">Notification Categories</h2>
            {Object.keys(categoryLabels).map(cat => (
              <div key={cat} className="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                <span className="text-sm text-gray-700">{categoryLabels[cat]}</span>
                <button
                  onClick={() => toggleCategory(cat)}
                  className={`w-12 h-6 rounded-full transition-colors ${preferences.categories[cat] ? 'bg-[#1A5632]' : 'bg-gray-300'}`}
                >
                  <div className={`w-5 h-5 bg-white rounded-full shadow transform transition-transform ${preferences.categories[cat] ? 'translate-x-6' : 'translate-x-1'}`} />
                </button>
              </div>
            ))}
          </div>
        )}

        {preferences && (
          <button
            onClick={savePreferences}
            disabled={saving}
            className="w-full bg-[#1A5632] text-white rounded-2xl py-3 font-semibold"
          >
            {saving ? 'Saving...' : 'Save Preferences'}
          </button>
        )}

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
