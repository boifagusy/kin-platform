const API_BASE = import.meta.env.VITE_API_URL || "http://localhost:8000/api/v1";
import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import ScreenLayout from '../../design-system/layouts/ScreenLayout';
import Card from '../../design-system/components/Card';
import Button from '../../design-system/components/Button';
import PageMotion from '../../motion/page';

function CheckInSettingsScreen() {
  const navigate = useNavigate();
  const phone = localStorage.getItem("kin_phone");

  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [settings, setSettings] = useState({
    checkin_time: "21:00",
    grace_minutes: 15,
    enabled: true,
  });
  const [message, setMessage] = useState(null);

  useEffect(() => {
    if (!phone) {
      navigate("/login");
      return;
    }
    fetchSettings();
  }, [phone]);

  const fetchSettings = async () => {
    try {
      const response = await fetch(`${API_BASE}/checkin-settings?phone=${encodeURIComponent(phone)}`, {
        headers: {
          "Authorization": `Bearer ${localStorage.getItem("kin_token")}`,
          "Accept": "application/json",
        },
      });
      const data = await response.json();
      if (data.success) {
        setSettings({
          checkin_time: data.data.checkin_time.substring(0, 5),
          grace_minutes: data.data.grace_minutes,
          enabled: data.data.enabled,
        });
      }
    } catch (error) {
      console.error("Error fetching settings:", error);
    } finally {
      setLoading(false);
    }
  };

  const saveAndContinue = async () => {
    setSaving(true);
    setMessage(null);

    try {
      const response = await fetch(`${API_BASE}/checkin-settings`, {
        method: "POST",
        headers: { "Content-Type": "application/json", "Authorization": `Bearer ${localStorage.getItem("kin_token")}`, "Accept": "application/json" },
        body: JSON.stringify({
          phone: phone,
          checkin_time: settings.checkin_time,
          grace_minutes: settings.grace_minutes,
          enabled: settings.enabled,
        }),
      });

      const data = await response.json();

      if (data.success) {
        navigate("/settings/duress-pin", { state: { phone: phone } });
      } else {
        setMessage({ type: "error", text: data.error || "Failed to save settings" });
        setSaving(false);
      }
    } catch (error) {
      setMessage({ type: "error", text: "Network error. Please try again." });
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-[#F0F7F2] flex items-center justify-center">
        <div className="text-center">
          <div className="w-10 h-10 border-3 border-[#1A5632] border-t-transparent rounded-full animate-spin mx-auto mb-3" />
          <p className="text-[#1A5632] text-sm">Loading settings...</p>
        </div>
      </div>
    );
  }

  return (
    <ScreenLayout>
      <PageMotion>
        <div className="bg-white px-5 py-4 border-b border-[#E9ECEF] sticky top-0 z-10">
          <div className="flex items-center gap-4">
            <button onClick={() => navigate(-1)} className="text-[#1A5632]">
              <span className="material-symbols-outlined">arrow_back</span>
            </button>
            <h1 className="text-lg font-bold text-[#1A5632]">Check-In Settings</h1>
          </div>
        </div>

        <div className="px-5 pt-4 pb-24 space-y-4 max-w-md mx-auto">

          {/* Enable/Disable Card */}
          <Card>
            <div className="flex justify-between items-center">
              <div>
                <h3 className="font-semibold text-gray-900">Automatic Check-Ins</h3>
                <p className="text-xs text-gray-500 mt-1">Enable daily safety reminders</p>
              </div>
              <button
                onClick={() => setSettings({ ...settings, enabled: !settings.enabled })}
                className="text-3xl"
              >
                <span className="material-symbols-outlined">
                  {settings.enabled ? 'toggle_on' : 'toggle_off'}
                </span>
              </button>
            </div>
          </Card>

          {/* Check-In Time Card */}
          <Card>
            <div className="flex items-center gap-3 mb-4">
              <div className="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                <span className="material-symbols-outlined text-[#1A5632]">schedule</span>
              </div>
              <h3 className="font-semibold text-gray-900">Daily Check-In Time</h3>
            </div>
            <input
              type="time"
              value={settings.checkin_time}
              onChange={(e) => setSettings({ ...settings, checkin_time: e.target.value })}
              disabled={!settings.enabled}
              className="w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-xl outline-none"
              style={{
                background: settings.enabled ? "white" : "#f3f4f6",
                cursor: settings.enabled ? "pointer" : "not-allowed",
              }}
            />
            <p className="text-xs text-gray-500 mt-2">
              You'll receive a reminder at this time every day
            </p>
          </Card>

          {/* Grace Period Card */}
          <Card>
            <div className="flex items-center gap-3 mb-4">
              <div className="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
                <span className="material-symbols-outlined text-yellow-600">timer</span>
              </div>
              <h3 className="font-semibold text-gray-900">Grace Period</h3>
            </div>
            <select
              value={settings.grace_minutes}
              onChange={(e) => setSettings({ ...settings, grace_minutes: parseInt(e.target.value) })}
              disabled={!settings.enabled}
              className="w-full px-4 py-3 text-sm border-2 border-gray-200 rounded-xl outline-none"
              style={{
                background: settings.enabled ? "white" : "#f3f4f6",
                cursor: settings.enabled ? "pointer" : "not-allowed",
              }}
            >
              <option value="5">5 minutes</option>
              <option value="10">10 minutes</option>
              <option value="15">15 minutes (Recommended)</option>
              <option value="30">30 minutes</option>
              <option value="60">60 minutes</option>
            </select>
            <p className="text-xs text-gray-500 mt-2">
              After this period, your trusted contacts will be notified
            </p>
          </Card>

          {/* Info Card */}
          <div className="bg-green-50 border border-green-200 rounded-lg p-4">
            <p className="text-xs text-[#1A5632] leading-relaxed">
              💡 When enabled, KIN will:
              <br/>• Remind you to check in daily
              <br/>• Alert trusted contacts if you miss your check-in
              <br/>• Keep your safety network informed
            </p>
          </div>

          {/* Message */}
          {message && (
            <div className={`mt-4 p-3 rounded-lg text-xs text-center ${
              message.type === "success" 
                ? "bg-green-100 text-green-700" 
                : "bg-red-100 text-red-700"
            }`}>
              {message.text}
            </div>
          )}

          {/* Action Button */}
          <Button
            onClick={saveAndContinue}
            disabled={saving}
            variant="primary"
            size="lg"
            className="w-full mt-2"
          >
            {saving ? "Saving & Continuing..." : "→ Continue to Duress PIN"}
          </Button>

        </div>
      </PageMotion>
    </ScreenLayout>
  );
}

export default CheckInSettingsScreen;
