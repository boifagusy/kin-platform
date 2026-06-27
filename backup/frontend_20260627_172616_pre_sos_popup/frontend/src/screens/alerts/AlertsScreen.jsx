import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { FaArrowLeft, FaBell } from "react-icons/fa";

const API_BASE = import.meta.env.VITE_API_URL;

function AlertsScreen() {
  const navigate = useNavigate();
  const [activities, setActivities] = useState([]);
  const [loading, setLoading] = useState(true);

  // Get phone from localStorage (same as Dashboard)
  const phone = localStorage.getItem("kin_phone");

  // Get authentication headers
  const getAuthHeaders = () => {
    const token = localStorage.getItem('sanctum_token') || '';
    return {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
      'X-Requested-With': 'XMLHttpRequest',
    };
  };

  useEffect(() => {
    if (!phone) {
      setLoading(false);
      return;
    }

    fetch(`${API_BASE}/activities?phone=${encodeURIComponent(phone)}`, {
      headers: getAuthHeaders(),
    })
      .then(res => {
        if (!res.ok) {
          throw new Error(`HTTP ${res.status}`);
        }
        return res.json();
      })
      .then(data => {
        if (data.success && data.data && data.data.activities) {
          setActivities(data.data.activities);
        }
        setLoading(false);
      })
      .catch(err => {
        console.error(err);
        setLoading(false);
      });
  }, []);

  const getIcon = (type) => {
    switch (type) {
      case 'CHECKIN_SAFE': return '✅';
      case 'CHECKIN_MISSED': return '⚠️';
      case 'CHECKIN_REMINDER_SENT': return '🔔';
      case 'CHECKIN_ALERT_SENT': return '🚨';
      case 'DURESS_PIN_USED': return '⚠️🚨';
      default: return '📋';
    }
  };

  const getTitle = (type) => {
    switch (type) {
      case 'CHECKIN_SAFE': return 'Check-in confirmed';
      case 'CHECKIN_MISSED': return 'Missed check-in';
      case 'CHECKIN_REMINDER_SENT': return 'Reminder sent';
      case 'CHECKIN_ALERT_SENT': return 'Alert sent';
      case 'DURESS_PIN_USED': return 'Duress PIN used';
      default: return 'Safety update';
    }
  };

  if (loading) {
    return (
      <div style={{ minHeight: "100vh", background: "#F0F7F2", display: "flex", alignItems: "center", justifyContent: "center" }}>
        <div style={{ textAlign: "center" }}>
          <div style={{ width: 30, height: 30, border: "3px solid #1A5632", borderTop: "3px solid transparent", borderRadius: "50%", animation: "spin 1s linear infinite", margin: "0 auto 10px" }}></div>
          <p style={{ color: "#1A5632" }}>Loading alerts...</p>
        </div>
      </div>
    );
  }

  return (
    <div style={{ minHeight: "100vh", background: "#F8F9FA", paddingBottom: 80 }}>
      {/* Header */}
      <div style={{ background: "white", padding: "16px 20px", borderBottom: "1px solid #e5e7eb", position: "sticky", top: 0, zIndex: 10 }}>
        <div style={{ display: "flex", alignItems: "center", gap: 16 }}>
          <button onClick={() => navigate(-1)} style={{ background: "none", border: "none", cursor: "pointer", fontSize: 20 }}>←</button>
          <h1 style={{ fontSize: 20, fontWeight: "bold", color: "#1A5632", margin: 0 }}>Alerts</h1>
        </div>
        <p style={{ fontSize: 10, color: "#999", marginTop: 4 }}>Found {activities.length} activities</p>
      </div>

      <div style={{ padding: "16px 20px", maxWidth: 500, margin: "0 auto" }}>
        <h3 style={{ fontSize: 14, fontWeight: "bold", color: "#6B7280", marginBottom: 12, letterSpacing: 0.5 }}>ACTIVITY TIMELINE</h3>

        <div style={{ background: "white", borderRadius: 20, overflow: "hidden", boxShadow: "0 2px 8px rgba(0,0,0,0.05)" }}>
          {activities.length === 0 ? (
            <div style={{ padding: 48, textAlign: "center" }}>
              <FaBell style={{ fontSize: 32, color: "#D1D5DB", marginBottom: 12 }} />
              <p style={{ color: "#9CA3AF", margin: 0 }}>No alerts yet</p>
            </div>
          ) : (
            activities.map((activity, idx) => (
              <div key={activity.id}>
                <div style={{ padding: 16, display: "flex", gap: 12, borderBottom: idx < activities.length - 1 ? "1px solid #f0f0f0" : "none" }}>
                  <div style={{ width: 40, height: 40, borderRadius: 20, background: "#F3F4F6", display: "flex", alignItems: "center", justifyContent: "center", fontSize: 20 }}>
                    {getIcon(activity.type)}
                  </div>
                  <div style={{ flex: 1 }}>
                    <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 4 }}>
                      <h4 style={{ margin: 0, fontSize: 14, fontWeight: 600, color: "#1F2937" }}>{getTitle(activity.type)}</h4>
                      <span style={{ fontSize: 11, color: "#9CA3AF" }}>{activity.time_ago}</span>
                    </div>
                    <p style={{ margin: 0, fontSize: 13, color: "#6B7280", lineHeight: 1.4 }}>{activity.message}</p>
                  </div>
                </div>
              </div>
            ))
          )}
        </div>
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

export default AlertsScreen;
