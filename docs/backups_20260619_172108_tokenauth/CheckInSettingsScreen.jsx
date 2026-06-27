import { updateCheckin, updateStep, STEPS } from "../../services/onboardingDraftService";
import { useState, useEffect } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import { FaArrowLeft, FaClock, FaHourglassHalf, FaToggleOn, FaToggleOff } from "react-icons/fa";

function CheckInSettingsScreen() {
  const location = useLocation();
  const navigate = useNavigate();
  const phone = location.state?.phone || localStorage.getItem("kin_phone");
  const trustedContact = location.state?.trusted_contact;

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
      const response = await fetch(`http://127.0.0.1:8000/api/v1/checkin-settings?phone=${encodeURIComponent(phone)}`);
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
      const response = await fetch("http://127.0.0.1:8000/api/v1/checkin-settings", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          phone: phone,
          checkin_time: settings.checkin_time,
          grace_minutes: settings.grace_minutes,
          enabled: settings.enabled,
        }),
      });

      const data = await response.json();

      if (data.success) {
        navigate("/settings/duress-pin", { state: { phone: phone, trusted_contact: trustedContact } });
    // Save draft
    updateCheckin({ frequency: "daily", time: settings.checkin_time });
    updateStep(STEPS.DURESS);
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
      <div style={{ minHeight: "100vh", background: "#F0F7F2", display: "flex", alignItems: "center", justifyContent: "center" }}>
        <div style={{ textAlign: "center" }}>
          <div style={{ width: 40, height: 40, border: "3px solid #1A5632", borderTop: "3px solid transparent", borderRadius: "50%", animation: "spin 1s linear infinite", margin: "0 auto 10px" }}></div>
          <div style={{ color: "#1A5632" }}>Loading settings...</div>
        </div>
      </div>
    );
  }

  return (
    <div style={{ minHeight: "100vh", background: "#F0F7F2" }}>

      {/* Header */}
      <div style={{ background: "white", padding: "16px 20px", borderBottom: "1px solid #e5e7eb", position: "sticky", top: 0, zIndex: 10 }}>
        <div style={{ display: "flex", alignItems: "center", gap: 16 }}>
          <button onClick={() => navigate(-1)} style={{ background: "none", border: "none", cursor: "pointer" }}>
            <FaArrowLeft style={{ fontSize: 20, color: "#1A5632" }} />
          </button>
          <h1 style={{ fontSize: 20, fontWeight: "bold", color: "#1A5632", margin: 0 }}>Check-In Settings</h1>
        </div>
      </div>

      <div style={{ padding: "20px", maxWidth: 500, margin: "0 auto" }}>

        {/* Enable/Disable Card */}
        <div style={{ background: "white", borderRadius: 24, padding: 20, marginBottom: 16, boxShadow: "0 1px 3px rgba(0,0,0,0.1)" }}>
          <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
            <div>
              <h3 style={{ fontWeight: "bold", color: "#1A5632", margin: 0 }}>Automatic Check-Ins</h3>
              <p style={{ fontSize: 12, color: "#6b7280", marginTop: 4 }}>Enable daily safety reminders</p>
            </div>
            <button
              onClick={() => setSettings({ ...settings, enabled: !settings.enabled })}
              style={{ background: "none", border: "none", cursor: "pointer" }}
            >
              {settings.enabled ? (
                <FaToggleOn style={{ fontSize: 48, color: "#1A5632" }} />
              ) : (
                <FaToggleOff style={{ fontSize: 48, color: "#9ca3af" }} />
              )}
            </button>
          </div>
        </div>

        {/* Check-In Time Card */}
        <div style={{ background: "white", borderRadius: 24, padding: 20, marginBottom: 16, boxShadow: "0 1px 3px rgba(0,0,0,0.1)" }}>
          <div style={{ display: "flex", alignItems: "center", gap: 12, marginBottom: 16 }}>
            <div style={{ width: 40, height: 40, borderRadius: 20, background: "#e8f5e9", display: "flex", alignItems: "center", justifyContent: "center" }}>
              <FaClock style={{ fontSize: 20, color: "#1A5632" }} />
            </div>
            <h3 style={{ fontWeight: "bold", color: "#1A5632", margin: 0 }}>Daily Check-In Time</h3>
          </div>
          <input
            type="time"
            value={settings.checkin_time}
            onChange={(e) => setSettings({ ...settings, checkin_time: e.target.value })}
            disabled={!settings.enabled}
            style={{
              width: "100%",
              padding: "14px",
              fontSize: "18px",
              border: "2px solid #e5e7eb",
              borderRadius: 16,
              outline: "none",
              background: settings.enabled ? "white" : "#f3f4f6",
              cursor: settings.enabled ? "pointer" : "not-allowed",
            }}
          />
          <p style={{ fontSize: 12, color: "#6b7280", marginTop: 8 }}>
            You'll receive a reminder at this time every day
          </p>
        </div>

        {/* Grace Period Card */}
        <div style={{ background: "white", borderRadius: 24, padding: 20, marginBottom: 16, boxShadow: "0 1px 3px rgba(0,0,0,0.1)" }}>
          <div style={{ display: "flex", alignItems: "center", gap: 12, marginBottom: 16 }}>
            <div style={{ width: 40, height: 40, borderRadius: 20, background: "#fef3c7", display: "flex", alignItems: "center", justifyContent: "center" }}>
              <FaHourglassHalf style={{ fontSize: 20, color: "#D4A017" }} />
            </div>
            <h3 style={{ fontWeight: "bold", color: "#1A5632", margin: 0 }}>Grace Period</h3>
          </div>
          <select
            value={settings.grace_minutes}
            onChange={(e) => setSettings({ ...settings, grace_minutes: parseInt(e.target.value) })}
            disabled={!settings.enabled}
            style={{
              width: "100%",
              padding: "14px",
              fontSize: "16px",
              border: "2px solid #e5e7eb",
              borderRadius: 16,
              outline: "none",
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
          <p style={{ fontSize: 12, color: "#6b7280", marginTop: 8 }}>
            After this period, your trusted contacts will be notified
          </p>
        </div>

        {/* Info Card */}
        <div style={{ background: "#e8f5e9", borderRadius: 16, padding: 16, marginBottom: 20 }}>
          <p style={{ fontSize: 13, color: "#1A5632", margin: 0, lineHeight: 1.5 }}>
            💡 When enabled, KIN will:
            <br/>• Remind you to check in daily
            <br/>• Alert trusted contacts if you miss your check-in
            <br/>• Keep your safety network informed
          </p>
        </div>

        {/* Message */}
        {message && (
          <div style={{
            marginTop: 16,
            padding: 12,
            borderRadius: 12,
            background: message.type === "success" ? "#d1fae5" : "#fee2e2",
            color: message.type === "success" ? "#065f46" : "#991b1b",
            textAlign: "center",
            fontSize: 14,
          }}>
            {message.text}
          </div>
        )}

        {/* SINGLE BUTTON: Saves AND Continues */}
        <button
          onClick={saveAndContinue}
          disabled={saving}
          style={{
            width: "100%",
            padding: "16px",
            background: saving ? "#B7D4BF" : "linear-gradient(135deg, #D4A017 0%, #B8860B 100%)",
            color: "white",
            border: "none",
            borderRadius: 16,
            fontSize: 18,
            fontWeight: "bold",
            cursor: saving ? "not-allowed" : "pointer",
            opacity: saving ? 0.7 : 1,
            marginTop: 12,
            marginBottom: 20,
          }}
        >
          {saving ? "Saving & Continuing..." : "→ Continue to Duress PIN"}
        </button>

      </div>

      <style>{`
        @keyframes spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
        }
      `}</style>
    </div>
  );
}

export default CheckInSettingsScreen;
