import { useState, useEffect } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import { FaCheckCircle, FaPhoneAlt, FaLocationArrow, FaPaperPlane } from "react-icons/fa";
import CheckInHeader from "./components/CheckInHeader";
import StatusCard from "./components/StatusCard";
import SafeButton from "./components/SafeButton";
import AssistanceButton from "./components/AssistanceButton";
import EmergencyButton from "./components/EmergencyButton";
import CheckInStats from "./components/CheckInStats";
import EmergencyModal from "./components/EmergencyModal";

function CheckInScreen() {
  const location = useLocation();
  const navigate = useNavigate();
  const phone = location.state?.phone || localStorage.getItem("kin_phone");
  const [dashboard, setDashboard] = useState(null);
  const [loading, setLoading] = useState(true);
  const [checkInState, setCheckInState] = useState("default");
  const [showAssistanceOptions, setShowAssistanceOptions] = useState(false);
  const [showEmergencyConfirm, setShowEmergencyConfirm] = useState(false);
  const [lastCheckIn, setLastCheckIn] = useState(null);

  useEffect(() => {
    if (!phone) {
      navigate("/login");
      return;
    }

    async function loadDashboard() {
      try {
        const res = await fetch(`${API_BASE}/dashboard?phone=${encodeURIComponent(phone)}`);
        const data = await res.json();
        if (data.success) setDashboard(data);
      } catch (err) {
        console.error(err);
      } finally {
        setLoading(false);
      }
    }
    loadDashboard();
  }, [phone, navigate]);

  const tasks = dashboard?.tasks?.filter(t => !t.completed) || [];
  const locationEnabled = !tasks.find(t => t.id === "location");
  const homeZoneAdded = !tasks.find(t => t.id === "home_zone");
  const duressPinCreated = !tasks.find(t => t.id === "duress_pin");
  
  
  const actualSafetyScore = dashboard?.safety_score || 60;
  const displayScore = actualSafetyScore;

  const getScoreLabel = (score) => {
    if (score >= 90) return "Excellent";
    if (score >= 70) return "Good";
    if (score >= 50) return "Fair";
    return "Needs Attention";
  };

  const handleSafeCheckIn = () => {
    setCheckInState("safe");
    setLastCheckIn(new Date().toLocaleTimeString());
    setShowAssistanceOptions(false);
  };

  const handleNeedAssistance = () => {
    setCheckInState("assistance");
    setShowAssistanceOptions(true);
  };

  const handleEmergency = () => {
    setShowEmergencyConfirm(true);
  };

  const confirmEmergency = () => {
    setShowEmergencyConfirm(false);
    setCheckInState("emergency");
    alert("🚨 SOS ACTIVATED! Your trusted contacts are being notified.");
  };

  const handleCallContact = () => {
    alert("Calling your trusted contact...");
  };

  const handleShareLocation = () => {
    alert("Sharing your live location...");
  };

  const handleSendAlert = () => {
    alert("Alert sent to your safety network.");
  };

  if (loading) {
    return (
      <div style={{ minHeight: "100vh", background: "#F0F7F2", display: "flex", alignItems: "center", justifyContent: "center" }}>
        <div style={{ textAlign: "center" }}>
          <div style={{ width: 40, height: 40, border: "3px solid #1A5632", borderTop: "3px solid transparent", borderRadius: "50%", animation: "spin 1s linear infinite", margin: "0 auto 10px" }}></div>
          <div style={{ color: "#1A5632" }}>Loading KIN...</div>
        </div>
      </div>
    );
  }

  const hour = new Date().getHours();
  const greeting = hour < 12 ? "Morning" : hour < 18 ? "Afternoon" : "Evening";
  const userName = dashboard?.user?.name?.split(" ")[0] || "User";
  const nextCheckin = dashboard?.checkin?.next_time || "9:00 PM";
  const contactsCount = dashboard?.contacts?.count || 0;

  return (
    <div style={{ minHeight: "100vh", background: "#F0F7F2", paddingBottom: 80 }}>
      
      <CheckInHeader userName={userName} greeting={greeting} />
      
      <div style={{ padding: "16px 20px", maxWidth: 500, margin: "0 auto" }}>
        
        <StatusCard score={displayScore} label={getScoreLabel(displayScore)} />
        
        <div style={{ marginTop: 16, marginBottom: 16 }}>
          {checkInState === "safe" ? (
            <div style={{ background: "white", borderRadius: 24, padding: 20, textAlign: "center", boxShadow: "0 1px 3px rgba(0,0,0,0.1)" }}>
              <FaCheckCircle style={{ fontSize: 48, color: "#22c55e", marginBottom: 12 }} />
              <p style={{ fontSize: 18, fontWeight: "bold", color: "#1A5632", margin: 0 }}>Checked In ✓</p>
              <p style={{ fontSize: 12, color: "#6b7280", marginTop: 8 }}>Next check-in at {nextCheckin}</p>
              <button 
                onClick={() => setCheckInState("default")}
                style={{ marginTop: 16, background: "#f3f4f6", border: "none", padding: "8px 16px", borderRadius: 20, fontSize: 12, cursor: "pointer" }}
              >
                Update Status
              </button>
            </div>
          ) : (
            <div style={{ background: "white", borderRadius: 24, padding: 20, boxShadow: "0 1px 3px rgba(0,0,0,0.1)" }}>
              <p style={{ fontWeight: "bold", color: "#1A5632", fontSize: 16, marginBottom: 4 }}>Daily Safety Check</p>
              <p style={{ fontSize: 12, color: "#6b7280", marginBottom: 16 }}>How are you right now?</p>
              
              <div style={{ display: "flex", flexDirection: "column", gap: 10 }}>
                <SafeButton onClick={handleSafeCheckIn} />
                <AssistanceButton onClick={handleNeedAssistance} />
                <EmergencyButton onClick={handleEmergency} />
              </div>
            </div>
          )}
        </div>

        {showAssistanceOptions && (
          <div style={{ background: "#fefce8", borderRadius: 20, padding: 16, marginBottom: 16, border: "1px solid #fde047" }}>
            <p style={{ fontSize: 14, fontWeight: "bold", color: "#854d0e", marginBottom: 12 }}>Need Assistance?</p>
            <div style={{ display: "flex", flexDirection: "column", gap: 10 }}>
              <button onClick={handleCallContact} style={{ background: "white", border: "1px solid #e5e7eb", borderRadius: 12, padding: "12px 16px", display: "flex", alignItems: "center", gap: 12, cursor: "pointer", fontSize: 14 }}>
                <FaPhoneAlt style={{ color: "#1A5632" }} /> Call Trusted Contact
              </button>
              <button onClick={handleShareLocation} style={{ background: "white", border: "1px solid #e5e7eb", borderRadius: 12, padding: "12px 16px", display: "flex", alignItems: "center", gap: 12, cursor: "pointer", fontSize: 14 }}>
                <FaLocationArrow style={{ color: "#1A5632" }} /> Share Live Location
              </button>
              <button onClick={handleSendAlert} style={{ background: "white", border: "1px solid #e5e7eb", borderRadius: 12, padding: "12px 16px", display: "flex", alignItems: "center", gap: 12, cursor: "pointer", fontSize: 14 }}>
                <FaPaperPlane style={{ color: "#1A5632" }} /> Send Alert to Network
              </button>
            </div>
          </div>
        )}

        <CheckInStats nextCheckin={nextCheckin} contactsCount={contactsCount} />
      </div>

      {showEmergencyConfirm && (
        <EmergencyModal onConfirm={confirmEmergency} onCancel={() => setShowEmergencyConfirm(false)} />
      )}

      <style>{`
        @keyframes spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
        }
      `}</style>
    </div>
  );
}

export default CheckInScreen;
