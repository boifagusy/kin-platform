import { clearDraft } from "../../services/onboardingDraftService";
import backgroundLocation from "../../services/BackgroundLocationService";
import { useState, useEffect } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import HeaderV2 from "../../components/dashboard/HeaderV2";
import SafetyScoreCardMinimal from "../../components/dashboard/SafetyScoreCardMinimal";
import TrustedContactCard from "../../components/dashboard/TrustedContactCard";
import SafetyCheckCard from "../../components/dashboard/SafetyCheckCard";
import AssistanceOptions from "../../components/dashboard/AssistanceOptions";
import ActivityTimeline from "../../components/dashboard/ActivityTimeline";
import SetupCard from "../../components/dashboard/SetupCard";
import EmergencyModal from "../../components/dashboard/EmergencyModal";
import BottomNav from "../../components/dashboard/BottomNav";
import { getCurrentLocation, getBatteryLevel } from "../../utils/location";
import KinSafety from "../../capacitor/kin-safety";
import { startNotificationChecker, stopNotificationChecker, scheduleSOSNotification } from "../../services/notificationService";
import { enqueue, retryQueue } from "../../services/offlineQueueService";

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
  const [locationPermissionGranted, setLocationPermissionGranted] = useState(false);
  const [showAddZoneModal, setShowAddZoneModal] = useState(false);
  const [zoneName, setZoneName] = useState("");
  const [zoneAddress, setZoneAddress] = useState("");
  const [zoneSaving, setZoneSaving] = useState(false);
  const [zoneError, setZoneError] = useState("");

  const handleTaskClick = (task) => {
    if (task.id === "safe_zones") {
      setShowAddZoneModal(true);
    } else if (task.id === "trusted_contact") {
      navigate("/network", { state: { phone } });
    } else if (task.id === "duress_pin") {
      navigate("/settings/duress-pin", { state: { phone } });
    }
  };

  const handleAddZone = async () => {
    if (!zoneName.trim() || zoneSaving) return;
    setZoneSaving(true);
    setZoneError("");
    try {
      const response = await fetch(`${API_BASE}/safe-zones`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Authorization": `Bearer ${localStorage.getItem("kin_token")}`,
          "Accept": "application/json",
        },
        body: JSON.stringify({ name: zoneName, address: zoneAddress || null }),
      });
      const data = await response.json();
      if (data.success) {
        setShowAddZoneModal(false);
        setZoneName("");
        setZoneAddress("");
        const refreshRes = await fetch(`${API_BASE}/dashboard?phone=${encodeURIComponent(phone)}`, {
          headers: { "Authorization": `Bearer ${localStorage.getItem("kin_token")}`, "Accept": "application/json" },
        });
        const refreshData = await refreshRes.json();
        if (refreshData.success) setDashboard(refreshData.data);
      } else {
        setZoneError(data.error || "Failed to add safe zone");
      }
    } catch (err) {
      setZoneError("Could not reach the server.");
    } finally {
      setZoneSaving(false);
    }
  };

  useEffect(() => {
    if (navigator.permissions && navigator.permissions.query) {
      navigator.permissions.query({ name: "geolocation" }).then((result) => {
        setLocationPermissionGranted(result.state === "granted");
        result.onchange = () => setLocationPermissionGranted(result.state === "granted");
      }).catch(() => setLocationPermissionGranted(false));
    }
  }, []);
  const [activeTab, setActiveTab] = useState("home");

  // Trusted Contact handlers
  const handleShareInvite = () => {
    console.log("📤 Share invite clicked");
    alert("Share invite - coming soon!");
  };

  const handleReplaceContact = () => {
    console.log("🔄 Replace contact clicked");
    alert("Replace contact - coming soon!");
  };

  useEffect(() => {
    const handleOnline = () => {
      setOffline(false);
      retryQueue(API_BASE).then(({ sent, failed }) => {
        if (sent > 0) console.log(`Sent ${sent} queued request(s) after reconnecting`);
        if (failed > 0) console.warn(`${failed} queued request(s) still pending`);
      });
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
        if (data.success) setDashboard(data);
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
  const locationEnabled = locationPermissionGranted;
  const homeZoneAdded = !tasks.find(t => t.id === "safe_zones");
  const duressPinCreated = !tasks.find(t => t.id === "duress_pin");

  let calculatedScore = 60;
  if (locationEnabled) calculatedScore += 15;
  if (homeZoneAdded) calculatedScore += 15;
  if (duressPinCreated) calculatedScore += 10;
  if (checkInState === 'safe') calculatedScore = Math.min(calculatedScore + 5, 100);

  const actualSafetyScore = dashboard?.safety_score || calculatedScore;
  const displayScore = checkInState === 'safe' ? Math.min(actualSafetyScore + 5, 100) : actualSafetyScore;

  const getScoreLabel = (score) => {
    if (score >= 90) return "Excellent";
    if (score >= 70) return "Good";
    if (score >= 50) return "Fair";
    return "Needs Attention";
  };

  const contactsCount = dashboard?.user?.contacts_count || 0;
  const safeZones = dashboard?.safe_zones || [];
  const recentCheckIn = dashboard?.last_checkin || false;

  const breakdownItems = [
    { label: "Location Enabled", status: locationEnabled },
    { label: "Trusted Contact Added", status: dashboard?.has_verified_contact ?? (contactsCount > 0) },
    { label: "Safe Zones Added", status: homeZoneAdded },
    { label: "Duress PIN Created", status: duressPinCreated },
    { label: "Recent Check-In", status: recentCheckIn !== false },
  ];

  const handleSafeCheckInState = () => {
    setCheckInState("safe");
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

      const checkinBody = {
        phone: phone,
        status: "safe",
        latitude: locationData?.latitude,
        longitude: locationData?.longitude,
        battery_level: batteryLevel,
      };

      if (!navigator.onLine) {
        enqueue("/api/v1/checkin", checkinBody, "checkin");
        handleSafeCheckInState();
        alert("\u26a0\ufe0f No internet connection. Your check-in has been saved and will be sent automatically once you're back online.");
        return;
      }

      try {
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
            headers: { "Authorization": `Bearer ${localStorage.getItem("kin_token")}` },
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
        alert("\u26a0\ufe0f Could not reach the server. Your check-in has been saved and will be sent automatically once connection is restored.");
      }
    } catch (error) {
      console.error("Check-in error:", error);
      alert("Failed to check in. Please try again.");
    } finally {
      setCheckInLoading(false);
    }
  };

  const handleNeedAssistance = () => {
    setCheckInState("assistance");
    setShowAssistanceOptions(true);
  };

  const handleEmergency = () => {
    const hasContact = dashboard?.has_verified_contact ?? (contactsCount > 0);
    if (!hasContact) {
      alert("Add a trusted contact first to enable SOS.");
      return;
    }
    setShowEmergencyConfirm(true);
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

    // Get device safety context for false-alarm prevention
    let safetyContext = null;
    try {
      safetyContext = await KinSafety.getSafetyStatus();
    } catch (err) {
      console.warn("KinSafety unavailable:", err.message);
    }

    const sosBody = {
      phone: phone,
      latitude: locationData?.latitude,
      longitude: locationData?.longitude,
      accuracy: locationData?.accuracy,
      battery_level: safetyContext?.battery ?? batteryLevel,
      device_trust: safetyContext?.deviceTrust ?? null,
      device_fingerprint: safetyContext?.fingerprint ?? null,
      confidence: safetyContext?.confidence ?? null,
      network_type: safetyContext?.network ?? null,
    };

    // No network at all -- skip the request entirely and queue immediately.
    if (!navigator.onLine) {
      enqueue("/api/v1/sos", sosBody, "sos");
      setCheckInState("emergency");
      alert("\u26a0\ufe0f No internet connection. Your SOS has been saved and will be sent automatically once you're back online.");
      return;
    }

    try {
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
        scheduleSOSNotification("Trusted Contacts", "", data.data?.sos_id).catch((err) => console.warn("SOS notification error:", err));
        alert("\ud83d\udea8 SOS ACTIVATED! Your trusted contacts are being notified.");
      } else {
        alert(data.error || "Failed to send SOS");
      }
    } catch (error) {
      // Real network failure (e.g. request timed out, connection dropped
      // mid-flight) even though navigator.onLine said we were connected.
      console.error("SOS error:", error);
      enqueue("/api/v1/sos", sosBody, "sos");
      setCheckInState("emergency");
      alert("\u26a0\ufe0f Could not reach the server. Your SOS has been saved and will be sent automatically once connection is restored.");
    }
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
  const nextCheckin = dashboard?.settings?.checkin_time || "9:00 PM";

  return (
    <div className="min-h-screen bg-[#F0F7F2] pb-20">
      <HeaderV2 greeting={greeting} userName={userName} onBellClick={() => navigate("/alerts", { state: { phone } })} unreadCount={dashboard?.unread_alerts || 0} />

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
          onSafe={handleSafeCheckInWithLocation}
          onAssistance={handleNeedAssistance}
          onEmergency={handleEmergency}
          onUpdateStatus={() => setCheckInState("default")}
          loading={checkInLoading}
          hasTrustedContact={dashboard?.has_verified_contact ?? (contactsCount > 0)}
          onAddContact={() => navigate("/network", { state: { phone } })}
        />

        {showAssistanceOptions && (
          <AssistanceOptions
            onCall={handleCallContact}
            onShareLocation={handleShareLocation}
            onSendAlert={handleSendAlert}
          />
        )}

        {/* Trusted Contact Card */}
        <TrustedContactCard
          contact={dashboard?.trusted_contact}
          inviteStatus={dashboard?.invite_status}
          onShare={handleShareInvite}
          onReplace={handleReplaceContact}
        />

        {tasks.length > 0 && <SetupCard tasks={tasks} onTaskClick={handleTaskClick} />}

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
          <h3 className="text-sm font-semibold text-[#1A1A1A] mb-2">Safe Zones</h3>
          {safeZones.length > 0 ? (
            <div className="flex flex-wrap gap-2">
              {safeZones.map((zone, index) => (
                <span
                  key={index}
                  className={`px-3 py-1 rounded-full text-xs font-medium ${
                    zone.active
                      ? "bg-green-100 text-green-700 border border-green-500"
                      : "bg-gray-100 text-gray-500 border border-gray-300"
                  }`}
                >
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

      <BottomNav 
        onSOS={handleEmergency} 
        activeTab={activeTab} 
        onTabChange={setActiveTab} 
      />
    </div>
  );
}

export default DashboardScreenV2;
