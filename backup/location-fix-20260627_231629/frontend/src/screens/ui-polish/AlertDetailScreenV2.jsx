import { useState, useEffect } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import { FaArrowLeft, FaPhone, FaWhatsapp, FaMapMarkerAlt, FaCheckCircle, FaExclamationTriangle } from "react-icons/fa";

const API_BASE = import.meta.env.VITE_API_URL;

function AlertDetailScreenV2() {
  const navigate = useNavigate();
  const location = useLocation();
  const incidentFromState = location.state?.incident;
  const phone = localStorage.getItem("kin_phone");

  const [incident, setIncident] = useState(null);
  const [user, setUser] = useState(null);
  const [locationData, setLocationData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // Auth check
  useEffect(() => {
    if (!phone) {
      navigate("/login");
      return;
    }
  }, [phone, navigate]);

  // Fetch incident details from backend
  useEffect(() => {
    if (!incidentFromState) {
      navigate("/alerts");
      return;
    }

    const fetchIncidentData = async () => {
      try {
        setLoading(true);
        setError(null);

        // Fetch incident details
        const incidentRes = await fetch(`${API_BASE}/incidents/${incidentFromState.id}`, {
          headers: {
            "Authorization": `Bearer ${localStorage.getItem("kin_token")}`,
            "Accept": "application/json",
          },
        });
        const incidentData = await incidentRes.json();

        if (incidentData.success) {
          setIncident(incidentData.data);
        } else {
          setError("Could not load incident details");
          return;
        }

        // Fetch user details
        const userRes = await fetch(`${API_BASE}/users/${incidentFromState.user_id}`, {
          headers: {
            "Authorization": `Bearer ${localStorage.getItem("kin_token")}`,
            "Accept": "application/json",
          },
        });
        const userData = await userRes.json();

        if (userData.success) {
          setUser(userData.data);
        }

        // Fetch location data
        const locationRes = await fetch(`${API_BASE}/location?phone=${encodeURIComponent(phone)}`, {
          headers: {
            "Authorization": `Bearer ${localStorage.getItem("kin_token")}`,
            "Accept": "application/json",
          },
        });
        const locationData = await locationRes.json();

        if (locationData.success && locationData.data) {
          setLocationData(locationData.data);
        }
      } catch (err) {
        console.error("Error fetching data:", err);
        setError("Unable to load details");
      } finally {
        setLoading(false);
      }
    };

    fetchIncidentData();
  }, [incidentFromState, phone, navigate]);

  const getIcon = (type) => {
    switch (type) {
      case "missed_checkin": return <FaExclamationTriangle className="text-yellow-500 text-2xl" />;
      case "sos": return <span className="text-2xl">🚨</span>;
      case "sos_triggered": return <span className="text-2xl">🚨</span>;
      default: return <span className="text-2xl">📋</span>;
    }
  };

  const getStatusColor = (status) => {
    switch (status) {
      case "active": return "bg-yellow-100 text-yellow-700";
      case "resolved": return "bg-green-100 text-green-700";
      default: return "bg-gray-100 text-gray-700";
    }
  };

  const getTypeLabel = (type) => {
    switch (type) {
      case "missed_checkin": return "Missed Check-In";
      case "sos": return "SOS Alert";
      case "sos_triggered": return "SOS Alert";
      default: return "Safety Alert";
    }
  };

  const handleCall = () => {
    if (user?.phone) {
      window.location.href = `tel:${user.phone}`;
    }
  };

  const handleWhatsApp = () => {
    if (user?.phone) {
      const cleaned = user.phone.replace(/\D/g, "");
      window.open(`https://wa.me/${cleaned}`, "_blank");
    }
  };

  const handleMap = () => {
    const lat = locationData?.latitude || incident?.location_lat;
    const lng = locationData?.longitude || incident?.location_lng;

    if (lat && lng) {
      window.open(`https://www.google.com/maps/search/?api=1&query=${lat},${lng}`, "_blank");
    } else {
      alert("Location not available for this incident.");
    }
  };

  const handleNavigate = () => {
    const lat = locationData?.latitude || incident?.location_lat;
    const lng = locationData?.longitude || incident?.location_lng;

    if (lat && lng) {
      window.open(`https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`, "_blank");
    } else {
      alert("Location not available for navigation.");
    }
  };

  const handleResolve = async () => {
    if (!incident) return;
    try {
      const response = await fetch(`${API_BASE}/incidents/${incident.id}/resolve`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Authorization": `Bearer ${localStorage.getItem("kin_token")}`,
          "Accept": "application/json",
        },
      });
      const data = await response.json();
      if (data.success) {
        navigate("/alerts");
      }
    } catch (error) {
      console.error("Error resolving incident:", error);
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-[#F0F7F2] flex items-center justify-center">
        <div className="text-center">
          <div className="w-10 h-10 border-3 border-[#1A5632] border-t-transparent rounded-full animate-spin mx-auto mb-3" />
          <p className="text-[#1A5632] text-sm">Loading...</p>
        </div>
      </div>
    );
  }

  if (error || !incident) {
    return (
      <div className="min-h-screen bg-[#F0F7F2] flex items-center justify-center px-6">
        <div className="bg-white rounded-2xl p-6 text-center shadow-sm border border-red-200">
          <p className="text-red-600 text-sm">{error || "Alert not found"}</p>
          <button
            onClick={() => navigate(-1)}
            className="mt-3 px-4 py-2 rounded-xl bg-[#1A5632] text-white text-sm font-medium"
          >
            Go Back
          </button>
        </div>
      </div>
    );
  }

  const hasLocation = locationData?.latitude || incident?.location_lat;

  return (
    <div className="min-h-screen bg-[#F0F7F2] pb-24">
      {/* Header */}
      <div className="bg-white px-5 py-4 border-b border-[#E9ECEF] sticky top-0 z-10">
        <div className="flex items-center gap-4">
          <button onClick={() => navigate(-1)} className="text-[#1A5632] text-xl">
            <FaArrowLeft />
          </button>
          <h1 className="text-lg font-bold text-[#1A5632]">Alert Details</h1>
        </div>
      </div>

      <div className="px-4 py-5 max-w-md mx-auto space-y-4">
        {/* Alert Card */}
        <div className="bg-white rounded-2xl p-6 shadow-sm border border-[#E9ECEF]">
          <div className="flex items-center gap-3 mb-4">
            <div className="w-12 h-12 rounded-full bg-[#F3F4F6] flex items-center justify-center">
              {getIcon(incident.type)}
            </div>
            <div>
              <h2 className="text-lg font-bold text-[#1A1A1A]">{getTypeLabel(incident.type)}</h2>
              <span className={`text-xs font-medium px-2 py-0.5 rounded-full ${getStatusColor(incident.status)}`}>
                {incident.status.charAt(0).toUpperCase() + incident.status.slice(1)}
              </span>
            </div>
          </div>

          <p className="text-sm text-[#6C757D]">{incident.message}</p>
          <p className="text-xs text-[#6C757D] mt-2">
            {incident.created_at ? new Date(incident.created_at).toLocaleString() : "Unknown time"}
          </p>
        </div>

        {/* Location Card */}
        <div className="bg-white rounded-2xl p-6 shadow-sm border border-[#E9ECEF]">
          <h3 className="text-sm font-semibold text-[#1A1A1A] mb-3">📍 Location</h3>
          {hasLocation ? (
            <div className="space-y-2">
              <p className="text-sm text-[#6C757D]">
                <span className="font-medium text-[#1A1A1A]">Latitude:</span> {locationData?.latitude || incident?.location_lat}
              </p>
              <p className="text-sm text-[#6C757D]">
                <span className="font-medium text-[#1A1A1A]">Longitude:</span> {locationData?.longitude || incident?.location_lng}
              </p>
              {locationData?.accuracy && (
                <p className="text-sm text-[#6C757D]">
                  <span className="font-medium text-[#1A1A1A]">Accuracy:</span> {locationData.accuracy}m
                </p>
              )}
              {locationData?.timestamp && (
                <p className="text-xs text-[#6C757D]">
                  Updated: {new Date(locationData.timestamp).toLocaleString()}
                </p>
              )}
            </div>
          ) : (
            <p className="text-sm text-[#6C757D]">No location data available</p>
          )}
        </div>

        {/* User Info */}
        <div className="bg-white rounded-2xl p-6 shadow-sm border border-[#E9ECEF]">
          <h3 className="text-sm font-semibold text-[#1A1A1A] mb-3">User Information</h3>
          {user ? (
            <div className="space-y-2">
              <p className="text-sm text-[#1A1A1A]"><span className="text-[#6C757D]">Name:</span> {user.name}</p>
              <p className="text-sm text-[#1A1A1A]"><span className="text-[#6C757D]">Phone:</span> {user.phone}</p>
            </div>
          ) : (
            <p className="text-sm text-[#6C757D]">User information not available</p>
          )}
        </div>

        {/* Actions */}
        <div className="bg-white rounded-2xl p-6 shadow-sm border border-[#E9ECEF]">
          <h3 className="text-sm font-semibold text-[#1A1A1A] mb-3">Actions</h3>
          <div className="space-y-3">
            {hasLocation && (
              <>
                <button
                  onClick={handleMap}
                  className="w-full h-12 rounded-xl bg-[#1A5632] text-white font-semibold text-sm flex items-center justify-center gap-2 hover:opacity-90 active:scale-95 transition-all"
                >
                  <FaMapMarkerAlt />
                  View on Map
                </button>
                <button
                  onClick={handleNavigate}
                  className="w-full h-12 rounded-xl bg-blue-500 text-white font-semibold text-sm flex items-center justify-center gap-2 hover:bg-blue-600 active:scale-95 transition-all"
                >
                  <FaMapMarkerAlt />
                  Navigate
                </button>
              </>
            )}
            <button
              onClick={handleCall}
              disabled={!user?.phone}
              className="w-full h-12 rounded-xl bg-green-500 text-white font-semibold text-sm flex items-center justify-center gap-2 hover:bg-green-600 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <FaPhone />
              Call {user?.name || "User"}
            </button>
            <button
              onClick={handleWhatsApp}
              disabled={!user?.phone}
              className="w-full h-12 rounded-xl bg-[#25D366] text-white font-semibold text-sm flex items-center justify-center gap-2 hover:opacity-90 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <FaWhatsapp />
              WhatsApp
            </button>
          </div>
        </div>

        {/* Resolve Button */}
        {incident.status === "active" && (
          <button
            onClick={handleResolve}
            className="w-full h-12 rounded-xl bg-[#1A5632] text-white font-semibold text-sm flex items-center justify-center gap-2 hover:opacity-90 active:scale-95 transition-all"
          >
            <FaCheckCircle />
            Resolve Alert
          </button>
        )}
      </div>
    </div>
  );
}

export default AlertDetailScreenV2;
