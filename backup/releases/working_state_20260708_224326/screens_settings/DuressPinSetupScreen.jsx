// Duress PIN Setup Screen - Silent emergency PIN
// When entered during login, silently triggers SOS to trusted contacts

import { useState, useEffect } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import { FaArrowLeft, FaLock, FaExclamationTriangle, FaShieldAlt, FaCheckCircle, FaEye, FaEyeSlash } from "react-icons/fa";

function DuressPinSetupScreen() {
  const location = useLocation();
  const navigate = useNavigate();
  const phone = location.state?.phone || localStorage.getItem("kin_phone");
  
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [duressPin, setDuressPin] = useState("");
  const [confirmPin, setConfirmPin] = useState("");
  const [showPin, setShowPin] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
  const [hasExisting, setHasExisting] = useState(false);
  const [showWarningModal, setShowWarningModal] = useState(false);
  
  useEffect(() => {
    if (!phone) {
      navigate("/login");
      return;
    }
    checkExistingDuressPin();
  }, [phone]);
  
  const checkExistingDuressPin = async () => {
    try {
      const response = await fetch(`${API_BASE}/duress-pin?phone=${encodeURIComponent(phone)}`, {
        headers: { "Authorization": `Bearer ${localStorage.getItem("kin_token")}` },
      });
      const data = await response.json();
      if (data.success) {
        setHasExisting(data.data.has_duress_pin);
      }
    } catch (error) {
      console.error("Error checking duress PIN:", error);
    } finally {
      setLoading(false);
    }
  };
  
  const saveDuressPin = async () => {
    if (duressPin.length !== 4) {
      setError("PIN must be 4 digits");
      return;
    }
    if (duressPin !== confirmPin) {
      setError("PINs do not match");
      return;
    }
    
    setSaving(true);
    setError("");
    
    try {
      const response = await fetch(`${import.meta.env.VITE_API_URL}/duress-pin", {
        method: "POST",
        headers: { "Content-Type": "application/json", "Authorization": `Bearer ${localStorage.getItem("kin_token")}` },
        body: JSON.stringify({
          phone: phone,
          duress_pin: duressPin,
        }),
      });
      
      const data = await response.json();
      
      if (data.success) {
        setShowWarningModal(true);
      } else {
        setError(data.message || "Failed to save duress PIN");
      }
    } catch (error) {
      setError("Network error. Please try again.");
    } finally {
      setSaving(false);
    }
  };
  
  const removeDuressPin = async () => {
    if (!confirm("Are you sure? This will disable silent emergency protection.")) {
      return;
    }
    
    setSaving(true);
    
    try {
      const response = await fetch(`${import.meta.env.VITE_API_URL}/duress-pin", {
        method: "DELETE",
        headers: { "Content-Type": "application/json", "Authorization": `Bearer ${localStorage.getItem("kin_token")}` },
        body: JSON.stringify({ phone: phone }),
      });
      
      const data = await response.json();
      
      if (data.success) {
        setHasExisting(false);
        setDuressPin("");
        setConfirmPin("");
        setSuccess("Duress PIN removed successfully");
        setTimeout(() => setSuccess(""), 3000);
      } else {
        setError(data.message || "Failed to remove duress PIN");
      }
    } catch (error) {
      setError("Network error. Please try again.");
    } finally {
      setSaving(false);
    }
  };
  
  const handleCloseModal = () => {
    setShowWarningModal(false);
    navigate("/dashboard", { state: { phone } });
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
  
  return (
    <div className="min-h-screen bg-[#F0F7F2] pb-24">
      
      {/* Header */}
      <div className="bg-white px-5 py-4 border-b border-gray-100 sticky top-0 z-10">
        <div className="flex items-center gap-4">
          <button onClick={() => navigate(-1)} className="cursor-pointer">
            <FaArrowLeft className="text-[#1A5632] text-xl" />
          </button>
          <h1 className="text-xl font-bold text-[#1A5632]">Duress PIN</h1>
        </div>
      </div>
      
      <div className="px-4 py-5 space-y-4 max-w-md mx-auto">
        
        {/* Warning Card */}
        <div className="bg-red-50 rounded-2xl p-5 border border-red-200">
          <div className="flex items-center gap-3 mb-3">
            <div className="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
              <FaExclamationTriangle className="text-red-500 text-xl" />
            </div>
            <h3 className="font-bold text-red-700">Emergency Only</h3>
          </div>
          <p className="text-sm text-red-600 mb-3">
            If you ever need to silently alert your trusted contacts, use this PIN instead of your normal PIN.
          </p>
          <p className="text-xs text-red-500">
            ⚠️ Your trusted contacts will be notified immediately, but your app will appear normal.
          </p>
        </div>
        
        {/* Info Card */}
        <div className="bg-blue-50 rounded-xl p-3">
          <p className="text-xs text-blue-700">
            💡 Duress PIN is different from your normal login PIN. When used, it silently triggers SOS.
          </p>
        </div>
        
        {hasExisting ? (
          // Show remove option
          <div className="bg-white rounded-2xl p-5 shadow-sm">
            <div className="flex items-center gap-3 mb-4">
              <div className="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                <FaCheckCircle className="text-green-600 text-xl" />
              </div>
              <div>
                <h3 className="font-bold text-[#1A5632]">Duress PIN Active</h3>
                <p className="text-xs text-gray-500">Your silent emergency PIN is set</p>
              </div>
            </div>
            <button
              onClick={removeDuressPin}
              disabled={saving}
              className="w-full py-3 rounded-xl bg-red-500 text-white font-semibold text-sm hover:bg-red-600 transition disabled:opacity-50"
            >
              {saving ? "Removing..." : "Remove Duress PIN"}
            </button>
          </div>
        ) : (
          // Create Duress PIN form
          <>
            <div className="bg-white rounded-2xl p-5 shadow-sm">
              <div className="flex items-center gap-3 mb-4">
                <div className="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                  <FaLock className="text-red-500 text-xl" />
                </div>
                <h3 className="font-bold text-[#1A5632]">Create Duress PIN</h3>
              </div>
              
              <div className="relative mb-4">
                <input
                  type={showPin ? "text" : "password"}
                  inputMode="numeric"
                  maxLength={4}
                  value={duressPin}
                  onChange={(e) => setDuressPin(e.target.value.replace(/\D/g, ""))}
                  placeholder="Enter 4-digit duress PIN"
                  className="w-full px-4 py-3 text-center text-2xl tracking-[0.5em] border border-gray-200 rounded-xl focus:outline-none focus:border-[#1A5632] focus:ring-1 focus:ring-[#1A5632]"
                />
                <button
                  type="button"
                  onClick={() => setShowPin(!showPin)}
                  className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"
                >
                  {showPin ? <FaEyeSlash /> : <FaEye />}
                </button>
              </div>
              <p className="text-xs text-gray-500 text-center mb-2">Choose a 4-digit code different from your login PIN</p>
              
              <input
                type={showPin ? "text" : "password"}
                inputMode="numeric"
                maxLength={4}
                value={confirmPin}
                onChange={(e) => setConfirmPin(e.target.value.replace(/\D/g, ""))}
                placeholder="Confirm duress PIN"
                className="w-full px-4 py-3 text-center text-2xl tracking-[0.5em] border border-gray-200 rounded-xl focus:outline-none focus:border-[#1A5632] focus:ring-1 focus:ring-[#1A5632]"
              />
              
              {error && (
                <p className="text-red-500 text-sm text-center mt-3">{error}</p>
              )}
              {success && (
                <p className="text-green-600 text-sm text-center mt-3">{success}</p>
              )}
            </div>
            
            <button
              onClick={saveDuressPin}
              disabled={saving || duressPin.length !== 4 || confirmPin.length !== 4}
              className="w-full py-4 rounded-xl bg-gradient-to-r from-[#1A5632] to-[#2F6A44] text-white font-semibold disabled:opacity-50 transition"
            >
              {saving ? "Saving..." : "Save Duress PIN"}
            </button>
          </>
        )}
        
        {/* Warning Info */}
        <div className="bg-gray-50 rounded-xl p-4">
          <p className="text-xs text-gray-600">
            <strong className="text-red-500">⚠️ Important:</strong> When you use your duress PIN, 
            KIN will still log you in normally, but your trusted contacts will be silently alerted 
            with your location. This allows you to signal for help without alerting the person 
            forcing you to log in.
          </p>
        </div>
      </div>
      
      {/* Warning Modal */}
      {showWarningModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-2xl p-6 max-w-sm w-full">
            <div className="text-center mb-4">
              <div className="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-3">
                <FaExclamationTriangle className="text-red-500 text-2xl" />
              </div>
              <h3 className="text-xl font-bold text-gray-900 mb-2">Duress PIN Saved</h3>
              <p className="text-sm text-gray-600">
                If you ever need to silently alert your trusted contacts, use this PIN instead of your normal PIN.
              </p>
            </div>
            <div className="bg-red-50 rounded-xl p-3 mb-4">
              <p className="text-xs text-red-700 text-center">
                ⚠️ Your trusted contacts will be notified immediately
              </p>
            </div>
            <button
              onClick={handleCloseModal}
              className="w-full py-3 rounded-xl bg-[#1A5632] text-white font-semibold"
            >
              Got it
            </button>
          </div>
        </div>
      )}
    </div>
  );
}

export default DuressPinSetupScreen;
