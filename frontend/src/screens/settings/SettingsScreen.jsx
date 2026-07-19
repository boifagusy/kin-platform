import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { PageMotion, CardMotion, ListMotion, ListItemMotion, ModalBackdrop, ModalContent } from '../../motion';

const API_BASE = '/api/v1';

const NAV_ITEMS = [
  { section: 'Preferences', items: [
    { icon: 'notifications', label: 'Notifications', desc: 'Calls, alerts and reminders', action: 'notifications' },
    { icon: 'palette', label: 'Appearance', desc: 'Theme and display settings', action: null },
    { icon: 'language', label: 'Language', desc: 'English (UK)', action: null },
  ]},
  { section: 'Safety', items: [
    { icon: 'schedule', label: 'Check-In Settings', desc: 'Daily check-in time and grace period', path: '/settings/check-in' },
    { icon: 'contacts', label: 'Trusted Contacts', desc: 'Manage your safety network', path: '/trusted-contacts' },
    { icon: 'location_on', label: 'Safe Zones', desc: 'Manage your safe locations', path: '/settings/safe-zones' },
    { icon: 'warning', label: 'Emergency Settings', desc: 'SOS and duress configuration', path: '/duress-pin' },
  ]},
  { section: 'Support', items: [
    { icon: 'help', label: 'Help Centre', desc: 'FAQs and guides', action: null },
    { icon: 'flag', label: 'Report a Problem', desc: 'Tell us if something is wrong', action: null },
    { icon: 'info', label: 'About', desc: 'Version 1.0', action: null },
  ]},
];

function SettingsScreen() {
  const navigate = useNavigate();
  const [preferences, setPreferences] = useState(null);
  const [saving, setSaving] = useState(false);
  const [showNotifications, setShowNotifications] = useState(false);

  useEffect(() => { fetchPreferences(); }, []);

  const fetchPreferences = async () => {
    const token = localStorage.getItem('auth_token');
    const res = await fetch(`${API_BASE}/preferences/notifications`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    const data = await res.json();
    setPreferences(data);
  };

  const toggleChannel = (ch) => setPreferences(prev => ({
    ...prev, channels: { ...prev.channels, [ch]: !prev.channels[ch] }
  }));

  const toggleCategory = (cat) => setPreferences(prev => ({
    ...prev, categories: { ...prev.categories, [cat]: !prev.categories[cat] }
  }));

  const savePreferences = async () => {
    setSaving(true);
    const token = localStorage.getItem('auth_token');
    await fetch(`${API_BASE}/preferences/notifications`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` },
      body: JSON.stringify(preferences),
    });
    setSaving(false);
    setShowNotifications(false);
  };

  const channelLabels = { sms: 'SMS', email: 'Email', whatsapp: 'WhatsApp', push: 'Push' };
  const categoryLabels = { security: 'Security Alerts', marketing: 'Marketing', system: 'System' };

  return (
    <PageMotion className="min-h-screen bg-[#F0F7F2]">
      {/* Header */}
      <div className="px-5 pt-12 pb-6">
        <button onClick={() => navigate(-1)} className="text-[#1A5632] text-sm font-medium mb-4 flex items-center gap-1">
          <span className="material-symbols-rounded text-lg">arrow_back</span> Back
        </button>
        <h1 className="text-2xl font-bold text-gray-900">Settings</h1>
      </div>

      <div className="px-5 pb-32 max-w-md mx-auto space-y-6">
        {/* Profile Card */}
        <CardMotion className="bg-white rounded-2xl p-5 shadow-sm">
          <div className="flex items-center gap-4">
            <div className="w-14 h-14 rounded-full bg-[#1A5632] flex items-center justify-center">
              <span className="material-symbols-rounded text-white text-2xl">person</span>
            </div>
            <div className="flex-1">
              <p className="font-semibold text-gray-900 text-base">Sarah Okafor</p>
              <p className="text-sm text-gray-500">+234 812 345 6789</p>
            </div>
            <span className="material-symbols-rounded text-gray-400">chevron_right</span>
          </div>
        </CardMotion>

        {/* Safety Status */}
        <CardMotion className="bg-white rounded-2xl p-5 shadow-sm">
          <h3 className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Safety Status</h3>
          <div className="grid grid-cols-3 gap-3">
            {[
              { icon: 'schedule', label: 'Check-In', value: 'Active', color: 'text-emerald-600' },
              { icon: 'contacts', label: 'Contacts', value: '2', color: 'text-gray-700' },
              { icon: 'location_on', label: 'Safe Zones', value: 'Set', color: 'text-emerald-600' },
            ].map(item => (
              <div key={item.label} className="text-center">
                <span className={`material-symbols-rounded text-xl ${item.color}`}>{item.icon}</span>
                <p className="text-xs text-gray-500 mt-1">{item.label}</p>
                <p className={`text-sm font-semibold ${item.color}`}>{item.value}</p>
              </div>
            ))}
          </div>
        </CardMotion>

        {/* Navigation Sections */}
        {NAV_ITEMS.map(({ section, items }) => (
          <div key={section}>
            <h3 className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-1">{section}</h3>
            <ListMotion className="bg-white rounded-2xl shadow-sm overflow-hidden">
              {items.map((item) => (
                <ListItemMotion key={item.label}>
                  <button
                    onClick={() => {
                      if (item.action === 'notifications') setShowNotifications(true);
                      else if (item.path) navigate(item.path);
                    }}
                    className="w-full flex items-center gap-4 px-5 py-4 text-left hover:bg-gray-50 transition-colors"
                  >
                    <span className="material-symbols-rounded text-gray-500 text-xl">{item.icon}</span>
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-medium text-gray-900">{item.label}</p>
                      <p className="text-xs text-gray-400 truncate">{item.desc}</p>
                    </div>
                    <span className="material-symbols-rounded text-gray-300 text-lg">chevron_right</span>
                  </button>
                </ListItemMotion>
              ))}
            </ListMotion>
          </div>
        ))}

        {/* Sign Out */}
        <button className="w-full py-4 text-sm font-medium text-red-500 hover:bg-red-50 rounded-2xl transition-colors">
          Sign Out
        </button>
      </div>

      {/* Notifications Bottom Sheet */}
      {showNotifications && (
        <ModalBackdrop onClose={() => setShowNotifications(false)}>
          <ModalContent className="bg-white rounded-t-2xl w-full max-w-md absolute bottom-0 left-0 right-0 mx-auto p-6 max-h-[80vh] overflow-y-auto">
            <div className="w-10 h-1 bg-gray-300 rounded-full mx-auto mb-4" />
            <h2 className="text-lg font-bold text-gray-900 mb-4">Notifications</h2>

            {preferences && (
              <>
                <h3 className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Channels</h3>
                <div className="bg-gray-50 rounded-xl p-4 mb-4">
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

                <h3 className="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Categories</h3>
                <div className="bg-gray-50 rounded-xl p-4 mb-6">
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

                <button
                  onClick={savePreferences}
                  disabled={saving}
                  className="w-full bg-[#1A5632] text-white rounded-xl py-3 font-semibold text-sm"
                >
                  {saving ? 'Saving...' : 'Save Preferences'}
                </button>
              </>
            )}
          </ModalContent>
        </ModalBackdrop>
      )}
    </PageMotion>
  );
}

export default SettingsScreen;
