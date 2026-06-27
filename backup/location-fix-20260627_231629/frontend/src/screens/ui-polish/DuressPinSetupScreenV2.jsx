import { updateStep, STEPS } from "../../services/onboardingDraftService";
import { useState, useEffect } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import LoadingScreen from "../../components/ui/LoadingScreen";

const API_BASE = import.meta.env.VITE_API_URL || "http://localhost:8000";

function DuressPinSetupScreenV2() {
  const location = useLocation();
  const navigate = useNavigate();
  const phone = location.state?.phone || localStorage.getItem("kin_phone");

  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [duressPin, setDuressPin] = useState("");
  const [confirmPin, setConfirmPin] = useState("");
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
  const [hasExisting, setHasExisting] = useState(false);
  const [showWarningModal, setShowWarningModal] = useState(false);
  const [shake, setShake] = useState(false);
  const [isConfirming, setIsConfirming] = useState(false);

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
        headers: { "Authorization": `Bearer ${localStorage.getItem("kin_token")}`, "Accept": "application/json" },
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

  const handleNumberClick = (num) => {
    if (saving) return;
    
    if (!isConfirming) {
      if (duressPin.length < 4) {
        setDuressPin(prev => prev + num);
        setError("");
        if (duressPin.length + 1 === 4) {
          setTimeout(() => setIsConfirming(true), 300);
        }
      }
    } else {
      if (confirmPin.length < 4) {
        setConfirmPin(prev => prev + num);
        setError("");
      }
    }
  };

  const handleDeleteClick = () => {
    if (saving) return;
    
    if (!isConfirming) {
      if (duressPin.length > 0) {
        setDuressPin(prev => prev.slice(0, -1));
        setError("");
      }
    } else {
      if (confirmPin.length > 0) {
        setConfirmPin(prev => prev.slice(0, -1));
        setError("");
      } else {
        setIsConfirming(false);
      }
    }
  };

  const handleClearClick = () => {
    if (saving) return;
    if (!isConfirming) {
      setDuressPin("");
    } else {
      setConfirmPin("");
    }
    setError("");
  };

  const saveDuressPin = async () => {
    if (saving) return;
    
    if (duressPin.length !== 4) {
      setError("PIN must be 4 digits");
      setShake(true);
      setTimeout(() => setShake(false), 500);
      return;
    }
    if (duressPin !== confirmPin) {
      setError("PINs do not match");
      setShake(true);
      setTimeout(() => setShake(false), 500);
      return;
    }

    setSaving(true);
    setError("");

    try {
      const response = await fetch(`${API_BASE}/duress-pin`, {
        method: "POST",
        headers: { "Content-Type": "application/json", "Authorization": `Bearer ${localStorage.getItem("kin_token")}`, "Accept": "application/json", "Accept": "application/json" },
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
      setError("DEBUG: " + (error?.message || String(error)));
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
      const response = await fetch(`${API_BASE}/duress-pin`, {
        method: "DELETE",
        headers: { "Content-Type": "application/json", "Authorization": `Bearer ${localStorage.getItem("kin_token")}`, "Accept": "application/json", "Accept": "application/json" },
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
      setError("DEBUG: " + (error?.message || String(error)));
    } finally {
      setSaving(false);
    }
  };

  const handleCloseModal = async () => {
    setShowWarningModal(false);
    try {
      await fetch(`${API_BASE}/auth/complete-onboarding`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Authorization": `Bearer ${localStorage.getItem("kin_token")}`, "Accept": "application/json"
        },
        body: JSON.stringify({ phone })
      });
    } catch (err) {
      console.error("Failed to mark onboarding complete:", err);
    }
    navigate("/dashboard", { state: { phone }, replace: true });
    // Save draft
    updateStep(STEPS.DASHBOARD);
  };

  const displayPin = isConfirming ? confirmPin : duressPin;
  const displayLabel = isConfirming ? "Confirm Duress PIN" : "Create Duress PIN";
  const displaySubtext = isConfirming ? "Re-enter your duress PIN to confirm" : "Choose a 4-digit code different from your login PIN";

  if (loading) {
    return (
      <div className="fixed inset-0 bg-[#F0F7F2] flex items-center justify-center">
        <div className="text-center">
          <div className="w-10 h-10 border-3 border-[#1A5632] border-t-transparent rounded-full animate-spin mx-auto mb-3" />
          <p className="text-[#1A5632] text-sm">Loading...</p>
        </div>
      </div>
    );
  }

  return (
    <>
      <LoadingScreen open={saving} message="auth" />

      <div className="fixed inset-0 bg-gradient-to-br from-[#F0F7F2] to-[#E8F3EA] flex flex-col">
        <div className="absolute top-[-100px] left-[-100px] w-80 h-80 bg-[#1A5632]/10 rounded-full blur-3xl" />
        <div className="absolute bottom-[-100px] right-[-100px] w-80 h-80 bg-[#D4A017]/10 rounded-full blur-3xl" />

        {/* Back Button */}
        <button
          onClick={() => navigate(-1)}
          className="absolute top-6 left-6 z-10 w-10 h-10 rounded-full bg-white/80 backdrop-blur-sm shadow-md flex items-center justify-center active:scale-95 transition-all"
        >
          <span className="material-symbols-outlined text-[#1A5632] text-2xl">arrow_back</span>
        </button>

        <div className="flex-1 flex flex-col items-center justify-center px-6">
          <div className="w-full max-w-md">
            
            {/* Logo */}
            <div className="text-center mb-3">
              <div className="w-14 h-14 mx-auto mb-2 rounded-full bg-gradient-to-br from-[#1A5632] to-[#0E3A22] shadow-lg flex items-center justify-center">
                <span className="material-symbols-outlined text-white text-2xl">shield</span>
              </div>
              <h1 className="text-lg font-black text-[#1A5632] tracking-[0.2em]">KIN</h1>
              <p className="text-[#6C757D] text-[10px] mt-0.5">Personal Safety Network</p>
            </div>

            {hasExisting ? (
              // Show remove option
              <div className="bg-white rounded-2xl p-6 shadow-sm border border-[#E9ECEF]">
                <div className="text-center mb-4">
                  <div className="w-14 h-14 mx-auto mb-3 rounded-full bg-green-100 flex items-center justify-center">
                    <span className="material-symbols-outlined text-green-600 text-2xl">check_circle</span>
                  </div>
                  <h3 className="text-xl font-bold text-[#1A5632]">Duress PIN Active</h3>
                  <p className="text-xs text-[#6C757D]">Your silent emergency PIN is set</p>
                </div>
                <button
                  onClick={removeDuressPin}
                  disabled={saving}
                  className="w-full py-3 rounded-xl bg-red-500 text-white font-semibold text-sm hover:bg-red-600 transition disabled:opacity-50"
                >
                  {saving ? "Removing..." : "Remove Duress PIN"}
                </button>
                {success && (
                  <p className="text-green-600 text-sm text-center mt-3">{success}</p>
                )}
                {error && (
                  <p className="text-red-500 text-sm text-center mt-3">{error}</p>
                )}
              </div>
            ) : (
              // Create Duress PIN form
              <>
                {/* Warning Card */}
                <div className="bg-red-50 rounded-xl p-4 border border-red-200 mb-4">
                  <div className="flex items-center gap-3 mb-2">
                    <span className="material-symbols-outlined text-red-500 text-2xl">warning</span>
                    <h3 className="font-bold text-red-700 text-sm">Emergency Only</h3>
                  </div>
                  <p className="text-xs text-red-600">
                    When used, silently alerts your trusted contacts with your location.
                  </p>
                </div>

                {/* Heading */}
                <div className="text-center mb-4">
                  <h2 className="text-xl font-bold text-[#1A1A1A]">{displayLabel}</h2>
                  <p className="text-[#6C757D] text-xs mt-1">
                    {displaySubtext}
                  </p>
                </div>

                {/* PIN Dots - 4 digits */}
                <div className="flex justify-center gap-3 mb-3">
                  {[0, 1, 2, 3].map((index) => (
                    <div
                      key={index}
                      className={`w-12 h-12 rounded-xl bg-white shadow-md border-2 flex items-center justify-center transition-all ${
                        displayPin.length > index
                          ? "border-[#1A5632] bg-[#1A5632]/5"
                          : "border-[#E9ECEF]"
                      } ${shake ? "shake-animation" : ""}`}
                    >
                      {displayPin.length > index && (
                        <div className="w-3 h-3 rounded-full bg-[#1A5632] fill-animation" />
                      )}
                    </div>
                  ))}
                </div>

                <p className="text-[#6C757D] text-[10px] text-center mb-2">
                  {isConfirming ? "Confirm your duress PIN" : "Enter a 4-digit duress PIN"}
                </p>

                {error && (
                  <div className="text-center mb-3">
                    <p className="text-red-500 text-xs font-medium">{error}</p>
                  </div>
                )}

                {/* Numeric Keypad */}
                <div className="grid grid-cols-3 gap-3 mb-3 relative z-50">
                  {[1, 2, 3, 4, 5, 6, 7, 8, 9].map((num) => (
                    <button
                      key={num}
                      onClick={() => handleNumberClick(num.toString())}
                      className="h-14 rounded-xl bg-white shadow-md border text-2xl font-semibold active:scale-95 transition-all"
                    >
                      {num}
                    </button>
                  ))}
                  <button onClick={handleClearClick} className="h-14 rounded-xl bg-white shadow-md border text-sm font-medium text-[#6C757D] active:scale-95 transition-all">Clear</button>
                  <button onClick={() => handleNumberClick("0")} className="h-14 rounded-xl bg-white shadow-md border text-2xl font-semibold active:scale-95 transition-all">0</button>
                  <button onClick={handleDeleteClick} className="h-14 rounded-xl bg-white shadow-md border text-sm font-medium text-red-600 active:scale-95 transition-all relative z-50">Del</button>
                </div>

                {/* Save Button */}
                <button
                  onClick={saveDuressPin}
                  disabled={saving || duressPin.length !== 4 || confirmPin.length !== 4}
                  className={`w-full h-12 rounded-xl font-semibold text-base flex items-center justify-center gap-2 mt-2 ${
                    saving || duressPin.length !== 4 || confirmPin.length !== 4
                      ? "bg-[#B7D4BF] text-white cursor-not-allowed"
                      : "bg-gradient-to-r from-[#1A5632] to-[#0E3A22] text-white shadow-lg active:scale-95"
                  }`}
                >
                  {saving ? "Saving..." : "Save Duress PIN"}
                </button>

                {/* Info Text */}
                <div className="bg-gray-50 rounded-xl p-3 mt-4">
                  <p className="text-[10px] text-[#6C757D] text-center">
                    ⚠️ When you use your duress PIN, KIN will still log you in normally,
                    but your trusted contacts will be silently alerted with your location.
                  </p>
                </div>
              </>
            )}
          </div>
        </div>
      </div>

      {/* Warning Modal */}
      {showWarningModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-2xl p-6 max-w-sm w-full animate-scaleIn">
            <div className="text-center mb-4">
              <div className="w-14 h-14 mx-auto mb-3 rounded-full bg-red-100 flex items-center justify-center">
                <span className="material-symbols-outlined text-red-500 text-2xl">warning</span>
              </div>
              <h3 className="text-xl font-bold text-[#1A1A1A] mb-2">Duress PIN Saved</h3>
              <p className="text-sm text-[#6C757D]">
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

      <style>{`
        @keyframes shake { 0%,100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }
        @keyframes fill { 0% { transform: scale(0); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
        @keyframes scaleIn { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .shake-animation { animation: shake 0.3s ease-in-out; }
        .fill-animation { animation: fill 0.2s ease-out; }
        .animate-scaleIn { animation: scaleIn 0.2s ease-out; }
      `}</style>
    </>
  );
}

export default DuressPinSetupScreenV2;
