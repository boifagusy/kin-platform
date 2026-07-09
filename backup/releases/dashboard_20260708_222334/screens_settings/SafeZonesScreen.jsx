import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';

const API_BASE = import.meta.env.VITE_API_URL;

function SafeZonesScreen() {
  const navigate = useNavigate();
  const [zones, setZones] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [showModal, setShowModal] = useState(false);
  const [newZone, setNewZone] = useState({ name: '', address: '' });
  const [submitting, setSubmitting] = useState(false);

  // Fetch safe zones
  const fetchZones = async () => {
    try {
      const token = localStorage.getItem('kin_token');
      const response = await fetch(`${API_BASE}/safe-zones`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });
      const data = await response.json();
      if (data.success) {
        setZones(data.data.safe_zones || []);
      } else {
        setError(data.error || 'Failed to load safe zones');
      }
    } catch (err) {
      setError('Network error. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchZones();
  }, []);

  // Add new zone
  const handleAddZone = async (e) => {
    e.preventDefault();
    if (!newZone.name.trim()) return;

    setSubmitting(true);
    try {
      const token = localStorage.getItem('kin_token');
      const response = await fetch(`${API_BASE}/safe-zones`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          name: newZone.name,
          address: newZone.address,
        }),
      });
      const data = await response.json();
      if (data.success) {
        setNewZone({ name: '', address: '' });
        setShowModal(false);
        await fetchZones();
      } else {
        setError(data.error || 'Failed to add safe zone');
      }
    } catch (err) {
      setError('Network error. Please try again.');
    } finally {
      setSubmitting(false);
    }
  };

  // Delete zone
  const handleDeleteZone = async (id) => {
    if (!confirm('Are you sure you want to delete this safe zone?')) return;

    try {
      const token = localStorage.getItem('kin_token');
      const response = await fetch(`${API_BASE}/safe-zones/${id}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });
      const data = await response.json();
      if (data.success) {
        await fetchZones();
      } else {
        setError(data.error || 'Failed to delete safe zone');
      }
    } catch (err) {
      setError('Network error. Please try again.');
    }
  };

  // Toggle zone active status (optional enhancement)
  const handleToggleZone = async (id, currentActive) => {
    // This would require a PATCH endpoint. For now, just a placeholder.
    // Future improvement: add PATCH /safe-zones/{id} to toggle active status.
    console.log('Toggle zone:', id, currentActive);
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-[#F0F7F2] flex items-center justify-center">
        <div className="text-center">
          <div className="w-8 h-8 border-3 border-[#1A5632] border-t-transparent rounded-full animate-spin mx-auto mb-3" />
          <p className="text-[#1A5632] text-sm">Loading safe zones...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-[#F0F7F2] pb-20">
      {/* Header */}
      <div className="bg-[#1A5632] px-5 pt-8 pb-4">
        <div className="flex items-center justify-between">
          <button onClick={() => navigate('/dashboard')} className="text-white">
            ← Back
          </button>
          <h1 className="text-white text-lg font-semibold">Safe Zones</h1>
          <button
            onClick={() => setShowModal(true)}
            className="text-white text-lg font-medium"
          >
            + Add
          </button>
        </div>
      </div>

      {/* Error Message */}
      {error && (
        <div className="mx-5 mt-4 p-3 bg-red-100 border border-red-300 rounded-lg">
          <p className="text-red-700 text-sm">{error}</p>
          <button
            onClick={() => setError(null)}
            className="text-red-700 text-xs font-medium mt-1"
          >
            Dismiss
          </button>
        </div>
      )}

      {/* Safe Zones List */}
      <div className="px-5 py-4 max-w-md mx-auto">
        {zones.length === 0 ? (
          <div className="bg-white rounded-2xl p-8 text-center border border-[#E9ECEF]">
            <div className="text-4xl mb-3">🛡️</div>
            <h3 className="text-sm font-semibold text-[#1A1A1A] mb-2">No Safe Zones Yet</h3>
            <p className="text-xs text-[#6C757D] mb-4">
              Add your first safe zone to improve your safety score.
            </p>
            <button
              onClick={() => setShowModal(true)}
              className="bg-[#1A5632] text-white px-6 py-2 rounded-lg text-sm font-medium"
            >
              Add Safe Zone
            </button>
          </div>
        ) : (
          <div className="space-y-3">
            {zones.map((zone) => (
              <div
                key={zone.id}
                className={`bg-white rounded-2xl p-4 border ${
                  zone.active ? 'border-[#1A5632]' : 'border-[#E9ECEF]'
                } shadow-sm`}
              >
                <div className="flex items-start justify-between">
                  <div className="flex-1">
                    <div className="flex items-center gap-2">
                      <span className={`w-2 h-2 rounded-full ${zone.active ? 'bg-green-500' : 'bg-gray-300'}`} />
                      <h3 className="text-sm font-semibold text-[#1A1A1A]">{zone.name}</h3>
                    </div>
                    {zone.address && (
                      <p className="text-xs text-[#6C757D] mt-1">{zone.address}</p>
                    )}
                    {zone.latitude && zone.longitude && (
                      <p className="text-xs text-[#6C757D] mt-1">
                        📍 {zone.latitude.toFixed(6)}, {zone.longitude.toFixed(6)}
                      </p>
                    )}
                    <span className="text-xs text-[#6C757D] mt-1 block">
                      {zone.active ? 'Active' : 'Inactive'}
                    </span>
                  </div>
                  <div className="flex flex-col gap-2">
                    <button
                      onClick={() => handleToggleZone(zone.id, zone.active)}
                      className={`text-xs px-3 py-1 rounded-full ${
                        zone.active
                          ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200'
                          : 'bg-green-100 text-green-700 hover:bg-green-200'
                      }`}
                    >
                      {zone.active ? 'Pause' : 'Activate'}
                    </button>
                    <button
                      onClick={() => handleDeleteZone(zone.id)}
                      className="text-xs bg-red-100 text-red-700 px-3 py-1 rounded-full hover:bg-red-200"
                    >
                      Delete
                    </button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      {/* Add Zone Modal */}
      {showModal && (
        <div className="fixed inset-0 bg-black/50 flex items-end justify-center z-50">
          <div className="bg-white w-full max-w-md rounded-t-3xl p-6 animate-slide-up">
            <div className="flex items-center justify-between mb-4">
              <h2 className="text-lg font-semibold text-[#1A1A1A]">Add Safe Zone</h2>
              <button
                onClick={() => setShowModal(false)}
                className="text-[#6C757D] text-2xl"
              >
                ×
              </button>
            </div>

            <form onSubmit={handleAddZone}>
              <div className="mb-4">
                <label className="block text-sm font-medium text-[#1A1A1A] mb-1">
                  Zone Name *
                </label>
                <input
                  type="text"
                  value={newZone.name}
                  onChange={(e) => setNewZone({ ...newZone, name: e.target.value })}
                  placeholder="e.g., Home, Work, School"
                  className="w-full p-3 border border-[#E9ECEF] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1A5632]"
                  required
                />
              </div>

              <div className="mb-4">
                <label className="block text-sm font-medium text-[#1A1A1A] mb-1">
                  Address (optional)
                </label>
                <input
                  type="text"
                  value={newZone.address}
                  onChange={(e) => setNewZone({ ...newZone, address: e.target.value })}
                  placeholder="123 Main St, City"
                  className="w-full p-3 border border-[#E9ECEF] rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#1A5632]"
                />
              </div>

              <button
                type="submit"
                disabled={submitting || !newZone.name.trim()}
                className={`w-full py-3 rounded-lg text-white font-medium ${
                  submitting || !newZone.name.trim()
                    ? 'bg-[#6C757D] cursor-not-allowed'
                    : 'bg-[#1A5632]'
                }`}
              >
                {submitting ? 'Adding...' : 'Add Safe Zone'}
              </button>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

export default SafeZonesScreen;
