import { useState, useEffect } from "react";
import { useNavigate, useParams } from "react-router-dom";
import ScreenLayout from '../../design-system/layouts/ScreenLayout';
import Card from '../../design-system/components/Card';
import Button from '../../design-system/components/Button';
import PageMotion from '../../motion/page';

const API_BASE = import.meta.env.VITE_API_URL;

function AlertDetailScreenV2() {
  const navigate = useNavigate();
  const { id } = useParams();
  const [incident, setIncident] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (!id) return;
    fetchIncident();
  }, [id]);

  const fetchIncident = async () => {
    try {
      setLoading(true);
      setError(null);
      const token = localStorage.getItem("kin_token");
      const res = await fetch(`${API_BASE}/incidents/${id}`, {
        headers: { Authorization: `Bearer ${token}`, Accept: "application/json" },
      });
      const data = await res.json();
      if (data.success) setIncident(data.data);
      else setError("Could not load incident details");
    } catch {
      setError("Unable to load details");
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-[#F0F7F2] flex items-center justify-center">
        <p className="text-[#1A5632] text-sm">Loading...</p>
      </div>
    );
  }

  if (error || !incident) {
    return (
      <div className="min-h-screen bg-[#F0F7F2]">
        <div className="bg-white px-5 py-4 border-b">
          <button onClick={() => navigate("/alerts")} className="text-[#1A5632] text-sm">← Back</button>
        </div>
        <div className="text-center py-12 px-5">
          <p className="text-red-500 text-sm mb-4">{error || "Incident not found"}</p>
          <button onClick={() => navigate("/alerts")} className="text-[#1A5632] text-sm font-medium">Back to Alerts</button>
        </div>
      </div>
    );
  }

  return (
    <ScreenLayout>
      <PageMotion>
        <div className="bg-white px-5 py-4 border-b border-[#E9ECEF]">
          <div className="flex items-center gap-4">
            <button onClick={() => navigate("/alerts")} className="text-[#1A5632]">
              <span className="material-symbols-outlined">arrow_back</span>
            </button>
            <h1 className="text-lg font-bold text-[#1A5632]">Alert Details</h1>
          </div>
        </div>
        <div className="px-5 pt-4 pb-24 space-y-4 max-w-md mx-auto">
          <Card>
            <h2 className="font-semibold text-gray-900">{incident.type || "Alert"}</h2>
            <p className="text-sm text-gray-500 mt-1">{incident.message || "No details"}</p>
            <p className="text-xs text-gray-400 mt-2">{new Date(incident.created_at).toLocaleString()}</p>
            <span className={`inline-block mt-2 text-xs font-medium px-2 py-0.5 rounded-full ${incident.status === "active" ? "text-red-500 bg-red-50" : "text-green-500 bg-green-50"}`}>
              {incident.status === "active" ? "Active" : "Resolved"}
            </span>
          </Card>
        </div>
      </PageMotion>
    </ScreenLayout>
  );
}

export default AlertDetailScreenV2;
