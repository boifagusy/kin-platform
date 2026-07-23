import { useState, useEffect } from "react";
import { createPortal } from "react-dom";

const API_BASE = import.meta.env.VITE_API_URL;

function CheckInPopupV2({ phone, message, checkinTime, onClose, onComplete }) {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [showEmergencyConfirm, setShowEmergencyConfirm] = useState(false);
  const [status, setStatus] = useState("idle");

  const handleSafe = async () => {
    setLoading(true);
    setError("");
    try {
      const response = await fetch(`${API_BASE}/checkin`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          phone: phone,
          status: "safe",
        }),
      });
      const data = await response.json();
      if (data.success) {
        setStatus("safe");
        setTimeout(() => {
          onComplete && onComplete();
          onClose && onClose();
        }, 1500);
      } else {
        setError(data.error || "Failed to check in");
      }
    } catch (err) {
      setError("Network error. Please try again.");
    } finally {
      setLoading(false);
    }
  };

  const handleAssistance = async () => {
    setLoading(true);
    setError("");
    try {
      const response = await fetch(`${API_BASE}/assistance`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          phone: phone,
          type: "location",
        }),
      });
      const data = await response.json();
      if (data.success) {
        setStatus("assistance");
        setTimeout(() => {
          onComplete && onComplete();
          onClose && onClose();
        }, 2000);
      } else {
        setError(data.error || "Failed to send assistance request");
      }
    } catch (err) {
      setError("Network error. Please try again.");
    } finally {
      setLoading(false);
    }
  };

  const handleEmergencyConfirm = async () => {
    setLoading(true);
    setError("");
    setShowEmergencyConfirm(false);
    try {
      const response = await fetch(`${API_BASE}/sos`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          phone: phone,
        }),
      });
      const data = await response.json();
      if (data.success) {
        setStatus("emergency");
        setTimeout(() => {
          onComplete && onComplete();
          onClose && onClose();
        }, 3000);
      } else {
        setError(data.error || "Failed to send SOS");
      }
    } catch (err) {
      setError("Network error. Please try again.");
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return createPortal(
      <div className="fixed inset-0 z-[99999] bg-black/60 backdrop-blur-sm flex items-center justify-center p-6">
        <div className="bg-white rounded-2xl p-8 w-full max-w-sm text-center">
          <div className="w-10 h-10 border-3 border-[#1A5632] border-t-transparent rounded-full animate-spin mx-auto mb-4" />
          <p className="text-[#6C757D] text-sm">Processing...</p>
        </div>
      </div>,
      document.body
    );
  }

  if (status === "safe") {
    return createPortal(
      <div className="fixed inset-0 z-[99999] bg-black/60 backdrop-blur-sm flex items-center justify-center p-6">
        <div className="bg-white rounded-2xl p-8 w-full max-w-sm text-center">
          <div className="w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 flex items-center justify-center">
            <span className="material-symbols-outlined text-3xl text-green-600">check_circle</span>
          </div>
          <h3 className="text-lg font-bold text-[#1A1A1A]">You're Safe</h3>
          <p className="text-sm text-[#6C757D] mt-2">Your trusted contacts have been notified.</p>
        </div>
      </div>,
      document.body
    );
  }

  if (status === "assistance") {
    return createPortal(
      <div className="fixed inset-0 z-[99999] bg-black/60 backdrop-blur-sm flex items-center justify-center p-6">
        <div className="bg-white rounded-2xl p-8 w-full max-w-sm text-center">
          <div className="w-16 h-16 mx-auto mb-4 rounded-full bg-yellow-100 flex items-center justify-center">
            <span className="material-symbols-outlined text-3xl text-yellow-600">warning</span>
          </div>
          <h3 className="text-lg font-bold text-[#1A1A1A]">Assistance Requested</h3>
          <p className="text-sm text-[#6C757D] mt-2">Help is on the way.</p>
        </div>
      </div>,
      document.body
    );
  }

  if (status === "emergency") {
    return createPortal(
      <div className="fixed inset-0 z-[99999] bg-black/60 backdrop-blur-sm flex items-center justify-center p-6">
        <div className="bg-white rounded-2xl p-8 w-full max-w-sm text-center">
          <div className="w-16 h-16 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center">
            <span className="material-symbols-outlined text-3xl text-red-600">emergency</span>
          </div>
          <h3 className="text-lg font-bold text-[#1A1A1A]">SOS Sent</h3>
          <p className="text-sm text-[#6C757D] mt-2">Your trusted contacts are being notified.</p>
        </div>
      </div>,
      document.body
    );
  }

  // Emergency Confirmation Modal
  if (showEmergencyConfirm) {
    return createPortal(
      <div className="fixed inset-0 z-[99999] bg-black/60 backdrop-blur-sm flex items-center justify-center p-6">
        <div className="bg-white rounded-2xl p-6 w-full max-w-sm shadow-2xl">
          <div className="w-16 h-16 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center">
            <span className="material-symbols-outlined text-3xl text-red-600">emergency</span>
          </div>
          <h3 className="text-xl font-bold text-[#1A1A1A] text-center mb-2">Emergency SOS</h3>
          <p className="text-sm text-[#6C757D] text-center mb-6">
            This will notify your trusted contacts and share your latest location.
          </p>
          <div className="flex gap-3">
            <button
              onClick={() => setShowEmergencyConfirm(false)}
              className="flex-1 h-12 rounded-xl bg-gray-100 text-[#6C757D] font-semibold text-base hover:bg-gray-200 active:scale-95 transition-all"
            >
              Cancel
            </button>
            <button
              onClick={handleEmergencyConfirm}
              className="flex-1 h-12 rounded-xl bg-red-500 text-white font-semibold text-base hover:bg-red-600 active:scale-95 transition-all"
            >
              Send SOS
            </button>
          </div>
        </div>
      </div>,
      document.body
    );
  }

  return createPortal(
    <div className="fixed inset-0 z-[99999] bg-black/60 backdrop-blur-sm flex items-center justify-center p-6">
      <div className="bg-white rounded-2xl p-6 w-full max-w-sm shadow-2xl">
        {/* Header */}
        <div className="text-center mb-4">
          <div className="w-12 h-12 mx-auto mb-2 rounded-full bg-[#E8F3EA] flex items-center justify-center">
            <span className="material-symbols-outlined text-2xl text-[#1A5632]">verified_user</span>
          </div>
          <h2 className="text-lg font-black text-[#1A5632] tracking-[0.15em]">KIN</h2>
          <p className="text-xs font-semibold text-[#6C757D] tracking-wider mt-1">DAILY SAFETY CHECK-IN</p>
        </div>

        {/* Message */}
        <p className="text-sm text-[#1A1A1A] text-center mb-4">
          {message || "Please confirm your status"}
        </p>

        {/* Error */}
        {error && (
          <div className="bg-red-50 p-3 rounded-xl mb-4">
            <p className="text-red-600 text-sm text-center">{error}</p>
          </div>
        )}

        {/* Actions */}
        <div className="space-y-3">
          <button
            onClick={handleSafe}
            disabled={loading}
            className="w-full h-14 rounded-xl bg-[#1A5632] text-white font-bold text-base shadow-lg shadow-[#1A5632]/20 hover:opacity-90 active:scale-95 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span className="material-symbols-outlined text-lg">check_circle</span>
            I'M SAFE
          </button>
          <button
            onClick={handleAssistance}
            disabled={loading}
            className="w-full h-14 rounded-xl bg-[#D4A017] text-white font-bold text-base shadow-lg shadow-[#D4A017]/20 hover:opacity-90 active:scale-95 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span className="material-symbols-outlined text-lg">warning</span>
            NEED ASSISTANCE
          </button>
          <button
            onClick={() => setShowEmergencyConfirm(true)}
            disabled={loading}
            className="w-full h-14 rounded-xl bg-red-500 text-white font-bold text-base shadow-lg shadow-red-500/30 hover:bg-red-600 active:scale-95 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span className="material-symbols-outlined text-lg">emergency</span>
            EMERGENCY SOS
          </button>
        </div>

        {/* Not Now */}
        <button
          onClick={onClose}
          disabled={loading}
          className="w-full mt-4 py-2 text-sm font-medium text-[#6C757D] hover:text-[#1A1A1A] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Not now
        </button>
      </div>
    </div>,
    document.body
  );
}

export default CheckInPopupV2;
