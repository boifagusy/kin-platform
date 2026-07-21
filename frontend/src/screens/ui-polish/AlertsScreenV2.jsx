import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import ScreenLayout from '../../design-system/layouts/ScreenLayout';
import Card from '../../design-system/components/Card';
import Button from '../../design-system/components/Button';
import PageMotion from '../../motion/page';

const API_BASE = import.meta.env.VITE_API_URL;

function AlertsScreenV2() {
  const navigate = useNavigate();
  const phone = localStorage.getItem("kin_phone");
  const [incidents, setIncidents] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [actionLoading, setActionLoading] = useState(null);
  const [feedback, setFeedback] = useState(null);

  const fetchIncidents = async () => {
    try {
      setError(null);
      const token = localStorage.getItem("kin_token");
      const url = phone
        ? `${API_BASE}/incidents?phone=${encodeURIComponent(phone)}`
        : `${API_BASE}/incidents`;
      const response = await fetch(url, {
        headers: { Authorization: `Bearer ${token}`, Accept: "application/json" },
      });
      if (!response.ok) throw new Error(`Failed to load alerts: ${response.status}`);
      const data = await response.json();
      setIncidents((data.data || []).filter(i => i.status !== 'resolved'));
    } catch (err) {
      setError("Unable to load alerts. Please try again.");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchIncidents(); }, []);

  const handleMarkRead = async (id) => {
    setActionLoading(id);
    try {
      const token = localStorage.getItem("kin_token");
      await fetch(`${API_BASE}/incidents/${id}/read`, {
        method: "PATCH",
        headers: { Authorization: `Bearer ${token}` },
      });
      setFeedback("Marked as read");
      fetchIncidents();
    } catch {
      setFeedback("Failed to mark as read");
    } finally {
      setActionLoading(null);
      setTimeout(() => setFeedback(null), 3000);
    }
  };

  const handleResolve = async (id) => {
    setActionLoading(id);
    try {
      const token = localStorage.getItem("kin_token");
      await fetch(`${API_BASE}/incidents/${id}/resolve`, {
        method: "POST",
        headers: { Authorization: `Bearer ${token}`, "Content-Type": "application/json" },
        body: JSON.stringify({ role: "trusted_contact" }),
      });
      setFeedback("Alert resolved");
      fetchIncidents();
    } catch {
      setFeedback("Failed to resolve");
    } finally {
      setActionLoading(null);
      setTimeout(() => setFeedback(null), 3000);
    }
  };

  const getIcon = (type) => {
    switch (type) {
      case "missed_checkin": return "warning";
      case "sos_triggered": return "emergency";
      case "test": return "notifications";
      default: return "description";
    }
  };

  const getLabel = (type) => {
    switch (type) {
      case "missed_checkin": return "Missed Check-In";
      case "sos_triggered": return "SOS Alert";
      case "test": return "Test Alert";
      default: return "Alert";
    }
  };

  const formatTime = (dateStr) => {
    if (!dateStr) return "";
    const date = new Date(dateStr);
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);
    if (date.toDateString() === today.toDateString()) return "Today " + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    if (date.toDateString() === yesterday.toDateString()) return "Yesterday";
    return date.toLocaleDateString();
  };

  const statusBadge = (status) => {
    if (status === "active") {
      return <span className="text-xs font-medium text-red-500 bg-red-50 px-2 py-0.5 rounded-full">Active</span>;
    }
    return <span className="text-xs font-medium text-green-500 bg-green-50 px-2 py-0.5 rounded-full">Resolved</span>;
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
    <ScreenLayout>
      <PageMotion>
        <div className="bg-white px-5 py-4 border-b border-[#E9ECEF] sticky top-0 z-10">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              <button onClick={() => navigate(-1)} className="text-[#1A5632]">
                <span className="material-symbols-outlined">arrow_back</span>
              </button>
              <h1 className="text-lg font-bold text-[#1A5632]">Alerts</h1>
            </div>
            <button
              onClick={fetchIncidents}
              title="Refresh alerts"
              className="w-9 h-9 rounded-full hover:bg-gray-100 flex items-center justify-center"
            >
              <span className="material-symbols-outlined text-[#1A5632]">refresh</span>
            </button>
          </div>
        </div>

        <div className="px-5 pt-4 pb-24 space-y-4 max-w-md mx-auto">
          {feedback && (
            <div className="bg-[#1A5632] text-white text-sm text-center py-2 rounded-xl">
              {feedback}
            </div>
          )}

          {error && (
            <div className="text-center py-12">
              <p className="text-red-500 text-sm mb-4">{error}</p>
              <Button variant="secondary" size="md" onClick={fetchIncidents}>Retry</Button>
            </div>
          )}

          {!error && incidents.length === 0 && (
            <div className="text-center py-12">
              <span className="material-symbols-outlined text-5xl text-gray-300 mb-4">inbox</span>
              <h3 className="text-base font-semibold text-gray-700">No Active Alerts</h3>
              <p className="text-sm text-gray-400 mt-1">You're all caught up.</p>
            </div>
          )}

          {incidents.map((incident) => (
            <Card key={incident.id}>
              <button
                onClick={() => navigate(`/alerts/${incident.id}`, { state: { incident, phone } })}
                className="w-full text-left"
              >
                <div className="flex items-start gap-3">
                  <span className="material-symbols-outlined text-2xl text-[#1A5632] mt-0.5">
                    {getIcon(incident.type)}
                  </span>
                  <div className="flex-1 min-w-0">
                    <div className="flex items-center justify-between">
                      <p className="font-semibold text-gray-900 text-sm">{getLabel(incident.type)}</p>
                      {statusBadge(incident.status)}
                    </div>
                    <p className="text-xs text-gray-500 mt-0.5">{incident.message || "No details"}</p>
                    <p className="text-xs text-gray-400 mt-1">{formatTime(incident.created_at)}</p>
                  </div>
                  <span className="material-symbols-outlined text-gray-300">chevron_right</span>
                </div>
              </button>

              <div className="flex gap-2 mt-3 pt-3 border-t border-gray-100">
                {incident.can_mark_read && (
                  <Button
                    variant="secondary"
                    size="sm"
                    onClick={(e) => { e.stopPropagation(); handleMarkRead(incident.id); }}
                    disabled={actionLoading === incident.id}
                    className="flex-1"
                  >
                    {actionLoading === incident.id ? "..." : "Mark Read"}
                  </Button>
                )}
                {incident.can_resolve && (
                  <Button
                    variant="primary"
                    size="sm"
                    onClick={(e) => { e.stopPropagation(); handleResolve(incident.id); }}
                    disabled={actionLoading === incident.id}
                    className="flex-1"
                  >
                    {actionLoading === incident.id ? "..." : "Resolve"}
                  </Button>
                )}
              </div>
            </Card>
          ))}
        </div>
      </PageMotion>
    </ScreenLayout>
  );
}

export default AlertsScreenV2;
