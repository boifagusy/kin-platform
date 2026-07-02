import { clearDraft } from "../../services/onboardingDraftService";
import backgroundLocation from "../../services/BackgroundLocationService";
import { useState, useEffect } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import HeaderV2 from "../../components/dashboard/HeaderV2";
import SafetyScoreCardMinimal from "../../components/dashboard/SafetyScoreCardMinimal";
import SafetyCheckCard from "../../components/dashboard/SafetyCheckCard";
import AssistanceOptions from "../../components/dashboard/AssistanceOptions";
import TrustedContactCard from "../../components/dashboard/TrustedContactCard";
import SetupCard from "../../components/dashboard/SetupCard";
import ActivityTimeline from "../../components/dashboard/ActivityTimeline";
import BottomNav from "../../components/dashboard/BottomNav";
import EmergencyModal from "../../components/dashboard/EmergencyModal";
import SOSBlockedPopup from "../../components/dashboard/SOSBlockedPopup";
import { startNotificationChecker, stopNotificationChecker, scheduleSOSNotification } from "../../services/notificationService";
import { enqueue, retryQueue } from "../../services/offlineQueueService";

const API_BASE = import.meta.env.VITE_API_URL;

function DashboardScreenV2() {
  const location = useLocation();
  const navigate = useNavigate();
  const phone = location.state?.phone || localStorage.getItem("kin_phone");
  const [dashboard, setDashboard] = useState(null);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState("home");
  const [checkInState, setCheckInState] = useState("default");
  const [checkInLoading, setCheckInLoading] = useState(false);
  const [showEmergencyConfirm, setShowEmergencyConfirm] = useState(false);
  const [showAssistanceOptions, setShowAssistanceOptions] = useState(false);
  const [offline, setOffline] = useState(!navigator.onLine);

  // ✅ SOS Blocked Popup State
  const [showSOSBlockedPopup, setShowSOSBlockedPopup] = useState(false);
  const [sosBlockedReason, setSosBlockedReason] = useState("");

  useEffect(() => {
    if (!phone) {
      navigate("/login");
      return;
    }

    const handleOnline = () => setOffline(false);
    const handleOffline = () => setOffline(true);
    window.addEventListener("online", handleOnline);
    window.addEventListener("offline", handleOffline);

    loadDashboard();

    startNotificationChecker(phone);
    backgroundLocation.start(phone);

    return () => {
      stopNotificationChecker();
      backgroundLocation.stop();
      window.removeEventListener("online", handleOnline);
      window.removeEventListener("offline", handleOffline);
    };
  }, [phone, navigate]);

  const loadDashboard = async () => {
    try {
      const response = await fetch(`${API_BASE}/dashboard?phone=${encodeURIComponent(phone)}`, {
        headers: {
          "Authorization": `Bearer ${localStorage.getItem("kin_token")}`,
          "Accept": "application/json",
        },
      });
      const data = await response.json();
      if (data.success) setDashboard(data);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const getScoreLabel = (score) => {
    if (score >= 80) return "Good";
    if (score >= 60) return "Fair";
    if (score >= 40) return "Low";
    return "Critical";
  };

  const handleCheckIn = async () => {
    setCheckInLoading(true);
    try {
      const checkinBody = { phone, status: "safe" };
      const response = await fetch(`${API_BASE}/checkin`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Authorization": `Bearer ${localStorage.getItem("kin_token")}`,
          "Accept": "application/json",
        },
        body: JSON.stringify(checkinBody),
      });
      const data = await response.json();
      if (data.success) {
        handleSafeCheckInState();
        const refreshRes = await fetch(`${API_BASE}/dashboard?phone=${encodeURIComponent(phone)}`, {
          headers: { "Authorization": `Bearer ${localStorage.getItem("kin_token")}`, "Accept": "application/json" },
        });
        const refreshData = await refreshRes.json();
        if (refreshData.success) setDashboard(refreshData);
      } else {
        alert(data.error || "Failed to check in");
      }
    } catch (fetchError) {
      console.error("Check-in network error:", fetchError);
      enqueue("/api/v1/checkin", checkinBody, "checkin");
      handleSafeCheckInState();
      alert("⚠️ Could not reach the server. Your check-in has been saved and will be sent automatically once connection is restored.");
    } finally {
      setCheckInLoading(false);
    }
  };

  const handleSafeCheckInState = () => {
    setCheckInState("success");
    setTimeout(() => setCheckInState("default"), 3000);
  };

  const handleEmergency = () => {
    setShowEmergencyConfirm(true);
  };

  const confirmEmergency = async () => {
    setShowEmergencyConfirm(false);
    setCheckInLoading(true);

    try {
      const locationData = await getLocation();
      const batteryLevel = await getBatteryLevel();

      const sosBody = {
        phone: phone,
        latitude: locationData?.latitude,
        longitude: locationData?.longitude,
        accuracy: locationData?.accuracy,
        battery_level: batteryLevel,
      };

      if (!navigator.onLine) {
        enqueue("/api/v1/sos", sosBody, "sos");
        setCheckInState("emergency");
        alert("⚠️ No internet connection. Your SOS has been saved and will be sent automatically once you're back online.");
        return;
      }

      const response = await fetch(`${API_BASE}/sos`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Authorization": `Bearer ${localStorage.getItem("kin_token")}`,
          "Accept": "application/json",
        },
        body: JSON.stringify(sosBody),
      });

      const data = await response.json();

      if (data.success) {
        setCheckInState("emergency");
        scheduleSOSNotification("Trusted Contacts", "", data.data?.sos_id);
        alert("🚨 SOS ACTIVATED! Your trusted contacts are being notified.");
      } else {
        // ✅ Check if the error is about missing trusted contacts
        if (data.error && data.error.includes("trusted contact")) {
          setSosBlockedReason(data.error);
          setShowSOSBlockedPopup(true);
        } else {
          alert(data.error || "Failed to send SOS");
        }
      }
    } catch (error) {
      console.error("SOS error:", error);
      enqueue("/api/v1/sos", sosBody, "sos");
      setCheckInState("emergency");
      alert("⚠️ Could not reach the server. Your SOS has been saved and will be sent automatically once connection is restored.");
    } finally {
      setCheckInLoading(false);
    }
  };

  const getLocation = () => {
    return new Promise((resolve) => {
      if (!navigator.geolocation) {
        resolve(null);
        return;
      }
      navigator.geolocation.getCurrentPosition(
        (pos) => resolve({ latitude: pos.coords.latitude, longitude: pos.coords.longitude, accuracy: pos.coords.accuracy }),
        () => resolve(null),
        { enableHighAccuracy: true, timeout: 10000 }
      );
    });
  };

  const getBatteryLevel = () => {
    return new Promise((resolve) => {
      if (!navigator.getBattery) {
        resolve(null);
        return;
      }
      navigator.getBattery().then((battery) => resolve(Math.round(battery.level * 100))).catch(() => resolve(null));
    });
  };

  const handleNeedAssistance = () => {
    setShowAssistanceOptions(!showAssistanceOptions);
  };

  const handleCallContact = () => alert("Calling your trusted contact...");
  const handleShareLocation = () => alert("Sharing your live location...");
  const handleSendAlert = () => alert("Alert sent to your safety network.");

  const handleShareInvite = () => {
    const inviteLink = `https://kin.app/invite?ref=${phone}`;
    if (navigator.share) {
      navigator.share({ title: "Join my KIN safety network", text: "Join my trusted safety network on KIN.", url: inviteLink }).catch(() => {});
    } else {
      navigator.clipboard.writeText(inviteLink).then(() => alert("Invite link copied!")).catch(() => alert("Could not copy link."));
    }
  };

  const handleReplaceContact = () => alert("Replace contact - Coming soon!");

  const displayScore = dashboard?.data?.safety_score ?? 60;
  const tasks = dashboard?.data?.tasks ?? [];
  const safeZones = dashboard?.data?.safe_zones ?? [];
  const breakdownItems = [
    { label: "Phone Verified", status: true },
    { label: "PIN Created", status: true },
    { label: "Trusted Contact", status: !!dashboard?.data?.trusted_contact },
    { label: "Duress PIN", status: !!dashboard?.data?.duress_pin_created },
  ];
  const duressPinCreated = !!dashboard?.data?.duress_pin_created;

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
  const userName = dashboard?.data?.user?.name?.split(" ")[0] || "User";
  const nextCheckin = dashboard?.settings?.checkin_time || "9:00 PM";

  return (
    <div className="min-h-screen bg-[#F0F7F2] pb-20">
      <HeaderV2 greeting={greeting} userName={userName} />

      {offline && (
        <div className="bg-yellow-100 p-3 text-center">
          <p className="text-xs font-medium text-yellow-800">⚠️ Offline Mode — Showing last known data</p>
        </div>
      )}

      <div className="px-5 py-4 max-w-md mx-auto space-y-4">
        <SafetyScoreCardMinimal score={displayScore} label={getScoreLabel(displayScore)} />

        <SafetyCheckCard
          checkInState={checkInState}
          nextCheckin={nextCheckin}
          onSafe={handleCheckIn}
          onAssistance={handleNeedAssistance}
          onEmergency={handleEmergency}
          onUpdateStatus={() => setCheckInState("default")}
          loading={checkInLoading}
        />

        {showAssistanceOptions && (
          <AssistanceOptions
            onCall={handleCallContact}
            onShareLocation={handleShareLocation}
            onSendAlert={handleSendAlert}
          />
        )}

        <TrustedContactCard
          contact={dashboard?.trusted_contact}
          inviteStatus={dashboard?.invite_status}
          onShare={handleShareInvite}
          onReplace={handleReplaceContact}
        />

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
          activities={dashboard?.data?.activities || []}
          totalCount={dashboard?.data?.activities_total || 0}
          onViewAll={() => alert("Full activity history — Coming soon!")}
        />

        <div className="bg-white rounded-2xl p-4 shadow-sm border border-[#E9ECEF]">
          <h3 className="text-sm font-semibold text-[#1A1A1A] mb-2">Safe Zones</h3>
          {safeZones.length > 0 ? (
            <div className="flex flex-wrap gap-2">
              {safeZones.map((zone, index) => (
                <span key={index} className="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 border border-green-500">
                  {zone.name}
                </span>
              ))}
            </div>
          ) : (
            <p className="text-xs text-[#6C757D]">No safe zones added yet.</p>
          )}
        </div>

        {!duressPinCreated && (
          <div className="bg-blue-50 rounded-2xl p-4 border border-blue-200">
            <p className="text-xs text-blue-700">💡 <span className="font-medium">Safety Tip:</span> Add a duress PIN for silent emergency alerts.</p>
          </div>
        )}
        {duressPinCreated && safeZones.length === 0 && (
          <div className="bg-blue-50 rounded-2xl p-4 border border-blue-200">
            <p className="text-xs text-blue-700">💡 <span className="font-medium">Safety Tip:</span> Add safe zones to improve safety monitoring.</p>
          </div>
        )}
      </div>

      {showEmergencyConfirm && (
        <EmergencyModal
          onConfirm={confirmEmergency}
          onCancel={() => setShowEmergencyConfirm(false)}
        />
      )}

      <BottomNav
        onSOS={handleEmergency}
        activeTab={activeTab}
        onTabChange={setActiveTab}
      />

      {/* ✅ SOS Blocked Popup */}
      <SOSBlockedPopup
        isOpen={showSOSBlockedPopup}
        onClose={() => setShowSOSBlockedPopup(false)}
        reason={sosBlockedReason}
      />
    </div>
  );
}

export default DashboardScreenV2;
