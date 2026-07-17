import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { FaArrowLeft, FaSync } from "react-icons/fa";

const API_BASE = import.meta.env.VITE_API_URL;

function AlertsScreenV2() {
  const navigate = useNavigate();
  const [incidents, setIncidents] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [unreadCount, setUnreadCount] = useState(0);

  const phone = localStorage.getItem("kin_phone") || "";

  const fetchIncidents = async () => {
    setLoading(true);
    setError(null);

    try {
      const url = `${API_BASE}/incidents?phone=${encodeURIComponent(phone)}`;

      const token = localStorage.getItem("kin_token");

      const response = await fetch(url, {
        headers: {
          "Content-Type": "application/json",
          "Authorization": token ? `Bearer ${token}` : '',
        },
      });

      if (!response.ok) {
        throw new Error(`Failed to load alerts: ${response.status}`);
      }

      const data = await response.json();

      if (data.success && data.data) {
        setIncidents(data.data.incidents || []);
        setUnreadCount(data.data.unread || 0);
      } else {
        setIncidents([]);
      }
    } catch (err) {
      console.error("Error fetching incidents:", err);
      setError("Unable to load alerts. Please try again.");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (phone) {
      fetchIncidents();
    } else {
      setError("No phone number found. Please log in again.");
      setLoading(false);
    }
  }, [phone]);

  const handleRefresh = () => {
    fetchIncidents();
  };

  const getIcon = (type) => {
    switch (type) {
      case "missed_checkin": return "⚠️";
      case "sos_triggered": return "🚨";
      case "test": return "🔔";
      default: return "📋";
    }
  };

  const getTimeGroup = (dateString) => {
    const date = new Date(dateString);
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    if (date.toDateString() === today.toDateString()) return 'Today';
    if (date.toDateString() === yesterday.toDateString()) return 'Yesterday';
    return 'Earlier';
  };

  const getStatusBadge = (status) => {
    if (status === 'active') {
      return <span className="inline-block text-xs font-medium text-red-500 bg-red-50 px-2 py-0.5 rounded-full">Active</span>;
    }
    return <span className="inline-block text-xs font-medium text-green-500 bg-green-50 px-2 py-0.5 rounded-full">Resolved</span>;
  };

  const getTitle = (type) => {
    switch (type) {
      case "missed_checkin": return "Missed Check-In";
      case "sos_triggered": return "SOS Triggered";
      case "test": return "Test Alert";
      default: return "Safety Alert";
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-[#F0F7F2] flex items-center justify-center">
        <div className="text-center">
          <div className="w-10 h-10 border-3 border-[#1A5632] border-t-transparent rounded-full animate-spin mx-auto mb-3" />
          <p className="text-[#1A5632] text-sm">Loading alerts...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-[#F0F7F2] pb-24">
      {/* Header */}
      <div className="bg-white px-5 py-4 border-b border-[#E9ECEF] sticky top-0 z-10">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-4">
            <button onClick={() => navigate(-1)} className="text-[#1A5632] text-xl">
              <FaArrowLeft />
            </button>
            <h1 className="text-lg font-bold text-[#1A5632]">Alerts</h1>
            {unreadCount > 0 && (
              <span className="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                {unreadCount}
              </span>
            )}
          </div>
          <button
            onClick={handleRefresh}
            className="p-2 rounded-full hover:bg-[#F0F7F2] transition-colors"
            title="Refresh alerts"
          >
            <FaSync className="text-[#1A5632] text-sm" />
          </button>
        </div>
      </div>

      <div className="px-4 py-5 max-w-md mx-auto">
        {error && (
          <div className="bg-red-50 rounded-2xl p-6 text-center border border-red-200">
            <p className="text-red-600 text-sm">{error}</p>
            <button
              onClick={handleRefresh}
              className="mt-3 px-4 py-2 rounded-xl bg-[#1A5632] text-white text-sm font-medium hover:opacity-90 transition"
            >
              Retry
            </button>
          </div>
        )}

        {!error && incidents.length === 0 && (
          <div className="bg-white rounded-2xl p-8 text-center shadow-sm border border-[#E9ECEF]">
            <div className="w-20 h-20 mx-auto mb-4 rounded-full bg-[#E8F3EA] flex items-center justify-center">
              <span className="material-symbols-outlined text-4xl text-[#1A5632]">shield</span>
            </div>
            <h3 className="text-lg font-semibold text-[#1A1A1A]">No Active Alerts</h3>
            <p className="text-sm text-[#6C757D] mt-1">You're safe. Safety incidents will appear here when they occur.</p>
          </div>
        )}

        {!error && incidents.length > 0 && (
          <div className="space-y-4">
            {['Today', 'Yesterday', 'Earlier'].map((group) => {
              const grouped = incidents
                .filter(i => getTimeGroup(i.created_at) === group)
                .sort((a, b) => {
                  if (a.status === 'active' && b.status !== 'active') return -1;
                  if (a.status !== 'active' && b.status === 'active') return 1;
                  return 0;
                });

              if (grouped.length === 0) return null;

              return (
                <div key={group}>
                  <h3 className="text-xs font-semibold text-[#6C757D] uppercase tracking-wider mb-2 px-1">
                    {group}
                  </h3>
                  <div className="bg-white rounded-2xl overflow-hidden shadow-sm border border-[#E9ECEF]">
                    {grouped.map((incident, idx) => (
                      <div
                        key={incident.id}
                        onClick={() => navigate("/alert-detail", { state: { incident } })}
                        className={`px-5 py-4 flex gap-3 cursor-pointer hover:bg-[#F8F9FA] transition-colors ${
                          idx < grouped.length - 1 ? "border-b border-[#E9ECEF]" : ""
                        }`}
                      >
                        <div className="w-10 h-10 rounded-full bg-[#F3F4F6] flex items-center justify-center text-xl flex-shrink-0">
                          {getIcon(incident.type)}
                        </div>
                        <div className="flex-1 min-w-0">
                          <div className="flex items-start justify-between gap-2">
                            <h4 className="text-sm font-semibold text-[#1F2937] truncate">
                              {getTitle(incident.type)}
                            </h4>
                            <span className="text-xs text-[#9CA3AF] whitespace-nowrap flex-shrink-0">
                              {new Date(incident.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                            </span>
                          </div>
                          <p className="text-sm text-[#6B728D] mt-0.5 line-clamp-2">
                            {incident.message}
                          </p>
                          {getStatusBadge(incident.status)}
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </div>
    </div>
  );
}

export default AlertsScreenV2;
