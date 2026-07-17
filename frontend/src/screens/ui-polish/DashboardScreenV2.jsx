import { clearDraft } from "../../services/onboardingDraftService";
import backgroundLocation from "../../services/BackgroundLocationService";
import { useState, useEffect } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import HeaderV2 from "../../components/dashboard/HeaderV2";
import SafetyScoreCardMinimal from "../../components/dashboard/SafetyScoreCardMinimal";
import SafeZonesCard from '../../components/dashboard/SafeZonesCard';
import AnnouncementBanner from '../../components/dashboard/AnnouncementBanner';
import SyncStatus from '../../components/dashboard/SyncStatus';
import TrustedContactCard from "../../components/dashboard/TrustedContactCard";
import SafetyCheckCard from "../../components/dashboard/SafetyCheckCard";
import AssistanceOptions from "../../components/dashboard/AssistanceOptions";
import ActivityTimeline from "../../components/dashboard/ActivityTimeline";
import SetupCard from "../../components/dashboard/SetupCard";
import EmergencyModal from "../../components/dashboard/EmergencyModal";
import BottomNav from "../../components/dashboard/BottomNav";
import { getCurrentLocation, getBatteryLevel } from "../../utils/location";
import { startNotificationChecker, stopNotificationChecker, scheduleSOSNotification } from "../../services/notificationService";
import safetyService from '../../services/SafetyService.js';

const API_BASE = import.meta.env.VITE_API_URL;

function DashboardScreenV2() {
  const location = useLocation();
  const navigate = useNavigate();
  const phone = location.state?.phone || localStorage.getItem("kin_phone");
  const [dashboard, setDashboard] = useState(null);
  const [loading, setLoading] = useState(true);
  const [checkInLoading, setCheckInLoading] = useState(false);
  const [checkInState, setCheckInState] = useState("default");
  const [showAssistanceOptions, setShowAssistanceOptions] = useState(false);
  const [showEmergencyConfirm, setShowEmergencyConfirm] = useState(false);
  const [offline, setOffline] = useState(!navigator.onLine);
  const [activeTab, setActiveTab] = useState("home");
  const [trustedContactStatus, setTrustedContactStatus] = useState(Date.now());

  // Trusted Contact handlers
  const handleShareInvite = () => {
    alert("Share invite - coming soon!");
  };

  const handleReplaceContact = () => {
    alert("Replace contact - coming soon!");
  };


  useEffect(() => {
    const handleOnline = () => {
      setOffline(false);
      // Auto-sync handled by SafetySyncManager
    };
    const handleOffline = () => setOffline(true);
    window.addEventListener("online", handleOnline);
    window.addEventListener("offline", handleOffline);
    return () => {
      window.removeEventListener("online", handleOnline);
      window.removeEventListener("offline", handleOffline);
    };
  }, []);

  useEffect(() => {
    if (!phone) {
      navigate("/login");
      return;
    }

    async function loadDashboard() {
      try {

const res = await fetch(`${API_BASE}/dashboard?phone=${encodeURIComponent(phone)}`, {


  headers: { "Authorization": `Bearer ${localStorage.getItem("kin_token")}` },
        });
        const data = await res.json();
        if (data.success) {
          setDashboard({
            ...data,
            trusted_contact: data.data?.trusted_contact || null,
            has_verified_contact: data.data?.has_verified_contact || false,
        });
        setCheckInState(data.data?.recent_checkin ? "done" : "idle");
        }
        // Clear onboarding draft
        clearDraft();
      } catch (err) {
        console.error(err);
      } finally {
        setLoading(false);
      }
    }
    loadDashboard();

    startNotificationChecker(phone);
    // Start background location tracking
    backgroundLocation.start(phone);

    return () => {
      stopNotificationChecker();
      backgroundLocation.stop();
    };
  }, [phone, navigate]);

  const tasks = dashboard?.pending_tasks || [];
  const locationEnabled = !tasks.find(t => t.id === "location");
  const homeZoneAdded = !tasks.find(t => t.id === "safe_zones");
  const duressPinCreated = !tasks.find(t => t.id === "duress_pin");


  const actualSafetyScore = dashboard?.data?.safety_score || 60;
  const displayScore = checkInState === 'safe' ? Math.min(actualSafetyScore + 5, 100) : actualSafetyScore;

  const getScoreLabel = (score) => {
    if (score >= 90) return "Excellent";
    if (score >= 70) return "Good";
    if (score >= 50) return "Fair";
    return "Needs Attention";
  };

  const contactsCount = dashboard?.user?.contacts_count || 0;
  const safeZones = dashboard?.data?.safe_zones || [];
  const recentCheckIn = dashboard?.data?.recent_checkin || false;

  const breakdownItems = [
    { label: "Location Enabled", status: locationEnabled },
    { label: "Trusted Contact Added", status: dashboard?.data?.has_verified_contact || false },
    { label: "Safe Zones Added", status: homeZoneAdded },
    { label: "Duress PIN Created", status: duressPinCreated },
    { label: "Recent Check-In", status: recentCheckIn !== false },
  ];

  const handleSafeCheckInState = () => {
    setCheckInState("done");
    setShowAssistanceOptions(false);
  };

  const handleSafeCheckInWithLocation = async () => {
    try {
      setCheckInLoading(true);

      let locationData = null;
      try {
        locationData = await getCurrentLocation();
      } catch (err) {
        console.warn("Location error:", err.message);
      }

      let batteryLevel = null;
      try {
        batteryLevel = await getBatteryLevel();
      } catch (err) {
        console.warn("Battery error:", err);
      }

      // Use SafetyService — single source of truth
      const result = await safetyService.checkIn(phone, locationData, batteryLevel);

      if (result.state === "SENT") {
        setCheckInState("done");
        const refreshRes = await fetch(`${API_BASE}/dashboard?phone=${encodeURIComponent(phone)}`, {
          headers: { "Authorization": `Bearer ${localStorage.getItem("kin_token")}` },
        });
        const refreshData = await refreshRes.json();
        if (refreshData.success) setDashboard(refreshData);
      } else if (result.state === "QUEUED") {
        setCheckInState("offline");
        handleSafeCheckInState();
        alert("📴 Check-in saved. Will send automatically when online.");
      } else {
        alert("Failed to check in. Please try again.");
      }

    } catch (error) {
      console.error("Check-in error:", error);
      console.error("  error.message:", error.message);
      console.error("  error.stack:", error.stack);
      alert("Failed to check in. Please try again.");
    } finally {
      setCheckInLoading(false);
    }
  };

  const confirmEmergency = async () => {
    setShowEmergencyConfirm(false);

    let locationData = null;
    try {
      locationData = await getCurrentLocation();
    } catch (err) {
      console.warn("Location error:", err);
    }

    let batteryLevel = null;
    try {
      batteryLevel = await getBatteryLevel();
    } catch (err) {
      console.warn("Battery error:", err);
    }

    // Use SafetyService — single source of truth
    const result = await safetyService.triggerSOS(phone, locationData, batteryLevel, { silent: false });

    setCheckInState("emergency");
    
      if (result.state === "SENT") {
        setCheckInState("done");
        const refreshRes = await fetch(`${API_BASE}/dashboard?phone=${encodeURIComponent(phone)}`, {
          headers: { "Authorization": `Bearer ${localStorage.getItem("kin_token")}` },
        });
        const refreshData = await refreshRes.json();
        if (refreshData.success) setDashboard(refreshData);
      } else if (result.state === "QUEUED") {
        setCheckInState("offline");
      alert("📴 SOS saved. Will send automatically when online.");
    } else {
      alert("Failed to send SOS. Please try again.");
    }
  };

  const handleNeedAssistance = () => {
    setCheckInState("assistance");
    setShowAssistanceOptions(true);
  };

  const handleEmergency = () => {
    setShowEmergencyConfirm(true);
  };

  const handleCallContact = () => alert("Calling your trusted contact...");
  const handleShareLocation = () => alert("Sharing your live location...");
  const handleSendAlert = () => alert("Alert sent to your safety network.");

  if (loading) {
    return (
      <div className="min-h-screen bg-[#F0F7F2] flex items-center justify-center">
        <div className="text-center">
          <div className="w-10 h-10 border-3 border-[#1A5632] border-t-transparent rounded-full animate-spin mx-auto mb-3" />
          <p className="text-[#1A5632] text-sm">Loading KIN...</p>
        </div>
      </div>
    );
  }

  const hour = new Date().getHours();
  const greeting = hour < 12 ? "Morning" : hour < 18 ? "Afternoon" : "Evening";
  const userName = dashboard?.data?.user?.name?.split(" ")[0] || dashboard?.user?.name?.split(" ")[0] || "User";
  const formatCheckinTime = (time) => {
  if (!time || time === "21:00") return "9:00 PM";
  const [h, m] = time.split(":");
  const hour = parseInt(h);
  const ampm = hour >= 12 ? "PM" : "AM";
  const displayHour = hour % 12 || 12;
  return `${displayHour}:${m} ${ampm}`;
};
const nextCheckin = formatCheckinTime(dashboard?.data?.settings?.checkin_time);

  return (
    <div className="min-h-screen bg-[#F0F7F2] pb-20">
      <SyncStatus />
      <HeaderV2 greeting={greeting} userName={userName} onBellClick={() => navigate("/alerts", { state: { phone } })} unreadCount={dashboard?.unread_alerts || 0} />

      {offline && (
        <div className="bg-yellow-100 p-3 text-center">
          <p className="text-xs font-medium text-yellow-800">⚠️ Offline Mode — Showing last known data</p>
        </div>
      )}

      <div className="px-5 py-4 max-w-md mx-auto space-y-4">
      <AnnouncementBanner />
        <SafetyScoreCardMinimal score={displayScore} label={getScoreLabel(displayScore)} />

        <SafetyCheckCard
          state={checkInState}
          nextCheckin={nextCheckin}
          hasTrustedContact={dashboard?.data?.has_verified_contact || false}
          hasActiveSOS={dashboard?.data?.has_active_sos || false}
          onSafe={handleSafeCheckInWithLocation}
          onAssistance={handleNeedAssistance}
          onEmergency={handleEmergency}
          onAddContact={() => navigate("/network")}
        />

        {showAssistanceOptions && (
          <AssistanceOptions
            onCall={handleCallContact}
            onShareLocation={handleShareLocation}
            onSendAlert={handleSendAlert}
          />
        )}

        {/* Trusted Contact Card */}
        {!dashboard?.data?.has_verified_contact || false && (
          <TrustedContactCard
            contact={dashboard?.data?.trusted_contact || null}
            inviteStatus={dashboard?.invite_status}
            onShare={handleShareInvite}
            onReplace={handleReplaceContact}
          />
        )}

        {tasks.length > 0 && <SetupCard tasks={tasks} />}

        <div className="bg-white rounded-2xl p-4 shadow-sm border border-[#E9ECEF]">
          <h3 className="text-sm font-semibold text-[#1A1A1A] mb-3">Safety Status</h3>
          <div className="space-y-2">
            {breakdownItems.map((item, index) => (
              <div key={index} className="flex items-center justify-between">
                <span className="text-xs text-[#1A1A1A]">{item.label}</span>
                <span className="text-sm">{item.status ? "✅" : "❌"}</span>
              </div>
            ))}
          </div>
        </div>

        <ActivityTimeline
          activities={dashboard?.activities || []}
          totalCount={dashboard?.activities_total || 0}
          onViewAll={() => alert("Full activity history — Coming soon!")}
        />

        <div className="bg-white rounded-2xl p-4 shadow-sm border border-[#E9ECEF]">
          <SafeZonesCard 
            zones={safeZones?.zones || safeZones || []} 
            count={safeZones?.count || (Array.isArray(safeZones) ? safeZones.length : 0)} 
          />
        </div>

        {!duressPinCreated && (
          <div className="bg-blue-50 rounded-2xl p-4 border border-blue-200">
            <p className="text-xs text-blue-700">
              💡 <span className="font-medium">Safety Tip:</span> Add a duress PIN for silent emergency alerts.
            </p>
          </div>
        )}
        {duressPinCreated && safeZones.length === 0 && (
          <div className="bg-blue-50 rounded-2xl p-4 border border-blue-200">
            <p className="text-xs text-blue-700">
              💡 <span className="font-medium">Safety Tip:</span> Add safe zones to improve safety monitoring.
            </p>
          </div>
        )}
      </div>

      {showEmergencyConfirm && (
        <EmergencyModal
          onConfirm={confirmEmergency}
          onCancel={() => setShowEmergencyConfirm(false)}
        />
      )}

      <BottomNav hasActiveSOS={dashboard?.data?.has_active_sos || false} hasTrustedContact={dashboard?.data?.has_verified_contact || false}
          hasActiveSOS={dashboard?.data?.has_active_sos || false}
        onSOS={handleEmergency}
        activeTab={activeTab}
        onTabChange={setActiveTab}
      />
    </div>
  );
}

export default DashboardScreenV2;

