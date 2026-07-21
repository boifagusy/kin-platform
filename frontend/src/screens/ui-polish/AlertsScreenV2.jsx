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
  const [expandedId, setExpandedId] = useState(null);
  const [newlyResolved, setNewlyResolved] = useState(null);
  const [resolveNote, setResolveNote] = useState("");
  const [showResolveConfirm, setShowResolveConfirm] = useState(null);

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
      const filtered = (data.data || []).filter(i => i.status !== 'resolved');
      filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
      setIncidents(filtered);
    } catch (err) {
      alert(`DEBUG alerts fetch failed:\nname: ${err.name}\nmessage: ${err.message}\ntoken present: ${!!token}\nAPI_BASE: ${API_BASE}`);
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
      setIncidents(prev => prev.map(i => i.id === id ? { ...i, read_at: new Date().toISOString(), can_mark_read: false } : i));
      setFeedback("Marked as read");
    } catch {
      setFeedback("Failed to mark as read");
    } finally {
      setActionLoading(null);
      setTimeout(() => setFeedback(null), 3000);
    }
  };

  const handleResolveConfirm = (id) => {
    setShowResolveConfirm(id);
    setResolveNote("");
  };

  const handleResolve = async (id) => {
    setActionLoading(id);
    try {
      const token = localStorage.getItem("kin_token");
      await fetch(`${API_BASE}/incidents/${id}/resolve`, {
        method: "POST",
        headers: { "Content-Type": "application/json", Authorization: `Bearer ${token}` },
        body: JSON.stringify({ role: "trusted_contact", note: resolveNote }),
      });
      setNewlyResolved(id);
      setShowResolveConfirm(null);
      setFeedback("Alert resolved — dismissed");
      setTimeout(() => {
        setIncidents(prev => prev.filter(i => i.id !== id));
        setNewlyResolved(null);
        setExpandedId(null);
      }, 2000);
    } catch {
      setFeedback("Failed to resolve");
    } finally {
      setActionLoading(null);
      setTimeout(() => setFeedback(null), 3000);
    }
  };

  const toggleExpand = (id) => {
    setExpandedId(expandedId === id ? null : id);
  };

  const isSOS = (incident) => incident.type === "sos_triggered";

  const getIcon = (type) => {
    switch (type) {
      case "missed_checkin": return "warning";
      case "sos_triggered": return "emergency";
      default: return "notifications";
    }
  };

  const getLabel = (type) => {
    switch (type) {
      case "missed_checkin": return "Missed Check-In";
      case "sos_triggered": return "SOS Emergency";
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

  const getMapsUrl = (incident) => {
    const lat = incident.latitude || incident.location_lat;
    const lng = incident.longitude || incident.location_lng;
    if (lat && lng) return `https://www.google.com/maps?q=${lat},${lng}`;
    return null;
  };

  const getUserPhone = (incident) => {
    return incident.user_phone || incident.phone || phone;
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
            <button onClick={fetchIncidents} title="Refresh" className="w-9 h-9 rounded-full hover:bg-gray-100 flex items-center justify-center">
              <span className="material-symbols-outlined text-[#1A5632]">refresh</span>
            </button>
          </div>
        </div>

        <div className="px-5 pt-4 pb-24 space-y-4 max-w-md mx-auto">
          {feedback && (
            <div className="bg-[#1A5632] text-white text-sm text-center py-2 rounded-xl">{feedback}</div>
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

          {incidents.map((incident) => {
            const isExpanded = expandedId === incident.id;
            const isEmergency = isSOS(incident);
            const mapsUrl = getMapsUrl(incident);
            const isResolvedPreview = newlyResolved === incident.id;
            const lat = incident.latitude || incident.location_lat;
            const lng = incident.longitude || incident.location_lng;
            const userPhone = getUserPhone(incident);

            return (
              <Card key={incident.id} className={`${isEmergency ? "border-l-4 border-l-red-500" : ""} ${isResolvedPreview ? "opacity-60 bg-green-50" : ""}`}>
                <button
                  onClick={() => toggleExpand(incident.id)}
                  className="w-full text-left"
                >
                  <div className="flex items-start gap-3">
                    <span className={`material-symbols-outlined text-2xl mt-0.5 ${isEmergency ? "text-red-500" : "text-[#1A5632]"}`}>
                      {getIcon(incident.type)}
                    </span>
                    <div className="flex-1 min-w-0">
                      <div className="flex items-center justify-between">
                        <p className={`font-semibold text-sm ${isEmergency ? "text-red-600" : "text-gray-900"}`}>
                          {getLabel(incident.type)}
                        </p>
                        <span className={`text-xs font-medium px-2 py-0.5 rounded-full ${isResolvedPreview ? "text-green-600 bg-green-100" : "text-red-500 bg-red-50"}`}>
                          {isResolvedPreview ? "Resolved" : "Active"}
                        </span>
                      </div>
                      <p className="text-xs text-gray-500 mt-0.5 truncate">{incident.message || "No details"}</p>
                      <p className="text-xs text-gray-400 mt-1">{formatTime(incident.created_at)}</p>
                    </div>
                    <span className="material-symbols-outlined text-gray-300 transition-transform" style={{ transform: isExpanded ? 'rotate(180deg)' : 'rotate(0deg)' }}>
                      expand_more
                    </span>
                  </div>
                </button>

                {isExpanded && (
                  <div className="mt-3 pt-3 border-t border-gray-100 space-y-3">
                    <div className="text-xs text-gray-500 space-y-1">
                      {lat && lng && (
                        <p>📍 {lat}, {lng}</p>
                      )}
                      <p>🕒 {new Date(incident.created_at).toLocaleString()}</p>
                      {incident.battery_level && <p>🔋 {incident.battery_level}%</p>}
                      {incident.message && <p className="mt-1">{incident.message}</p>}
                    </div>

                    {isEmergency && (
                      <div className="flex gap-2">
                        {mapsUrl && (
                          <a
                            href={mapsUrl}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="flex-1 flex items-center justify-center gap-1 py-2 rounded-xl bg-blue-50 text-blue-600 text-xs font-medium"
                          >
                            <span className="material-symbols-outlined text-sm">map</span> Open Maps
                          </a>
                        )}
                        {userPhone && (
                          <a
                            href={`tel:${userPhone}`}
                            className="flex-1 flex items-center justify-center gap-1 py-2 rounded-xl bg-green-50 text-green-600 text-xs font-medium"
                          >
                            <span className="material-symbols-outlined text-sm">call</span> Call
                          </a>
                        )}
                      </div>
                    )}

                    {showResolveConfirm === incident.id ? (
                      <div className="space-y-2">
                        <textarea
                          value={resolveNote}
                          onChange={(e) => setResolveNote(e.target.value)}
                          placeholder="Resolution note (optional)..."
                          className="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs"
                          rows={2}
                        />
                        <div className="flex gap-2">
                          <Button variant="secondary" size="sm" onClick={() => setShowResolveConfirm(null)} className="flex-1">Cancel</Button>
                          <Button variant="primary" size="sm" onClick={() => handleResolve(incident.id)} disabled={actionLoading === incident.id} className="flex-1">
                            {actionLoading === incident.id ? "..." : "Resolve SOS"}
                          </Button>
                        </div>
                      </div>
                    ) : (
                      <div className="flex gap-2">
                        {incident.can_mark_read && (
                          <Button variant="secondary" size="sm" onClick={(e) => { e.stopPropagation(); handleMarkRead(incident.id); }} disabled={actionLoading === incident.id} className="flex-1">
                            {actionLoading === incident.id ? "..." : "Mark Read"}
                          </Button>
                        )}
                        {incident.can_resolve && (
                          <Button variant="primary" size="sm" onClick={(e) => { e.stopPropagation(); handleResolveConfirm(incident.id); }} disabled={actionLoading === incident.id} className="flex-1">
                            {actionLoading === incident.id ? "..." : "Resolve SOS"}
                          </Button>
                        )}
                      </div>
                    )}
                  </div>
                )}
              </Card>
            );
          })}
        </div>
      </PageMotion>
    </ScreenLayout>
  );
}

export default AlertsScreenV2;
