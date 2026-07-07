import { updateStep, STEPS, getPhone } from "../../services/onboardingDraftService";
import { saveAuth } from "../../utils/auth";
import { useState, useEffect } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import LoadingScreen from "../../components/ui/LoadingScreen";

const API_BASE = import.meta.env.VITE_API_URL;

function CreatePinScreenV2() {
  const navigate = useNavigate();
  const location = useLocation();
  const phone = location.state?.phone || getPhone() || "";

  useEffect(() => {
    if (!phone) navigate("/login", { replace: true });
  }, [phone, navigate]);

  const [pin, setPin] = useState("");
  const [confirmPin, setConfirmPin] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);
  const [shake, setShake] = useState(false);
  const [isConfirming, setIsConfirming] = useState(false);

  const canContinue = pin.length === 4 && confirmPin.length === 4 && pin === confirmPin;
  const isPinComplete = pin.length === 4;

  // Auto-advance to confirm mode when PIN is complete
  const handleNumberClick = (num) => {
    if (loading) return;
    
    if (!isConfirming) {
      // Creating PIN
      if (pin.length < 4) {
        setPin(prev => prev + num);
        setError("");
        // Auto-advance to confirm when 4 digits entered
        if (pin.length + 1 === 4) {
          setTimeout(() => setIsConfirming(true), 300);
        }
      }
    } else {
      // Confirming PIN
      if (confirmPin.length < 4) {
        setConfirmPin(prev => prev + num);
        setError("");
      }
    }
  };

  const handleDeleteClick = () => {
    if (loading) return;
    
    if (!isConfirming) {
      if (pin.length > 0) {
        setPin(prev => prev.slice(0, -1));
        setError("");
      }
    } else {
      if (confirmPin.length > 0) {
        setConfirmPin(prev => prev.slice(0, -1));
        setError("");
      } else {
        // Go back to creating PIN if confirm is empty
        setIsConfirming(false);
      }
    }
  };

  const handleClearClick = () => {
    if (loading) return;
    if (!isConfirming) {
      setPin("");
    } else {
      setConfirmPin("");
    }
    setError("");
  };

  const handleContinue = async () => {
    if (loading || !canContinue) return;
    
    if (pin !== confirmPin) {
      setError("PINs do not match");
      setShake(true);
      setTimeout(() => setShake(false), 500);
      return;
    }

    try {
      setLoading(true);
      setError("");

      const response = await fetch(`${API_BASE}/auth/create-pin`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ phone, pin }),
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.error || "Unable to create PIN");
      }

      if (!data.token) {
        throw new Error("PIN created but no auth token was returned. Please try again.");
      }

      localStorage.setItem("kin_token", data.token);
      // Only advance the draft step once the token is confirmed saved
      updateStep(STEPS.USER_DETAILS);
      navigate("/user-details", { state: { phone } });
    } catch (error) {
      setError(error.message || "Unable to create PIN");
      setPin("");
      setConfirmPin("");
      setIsConfirming(false);
      setShake(true);
      setTimeout(() => setShake(false), 500);
    } finally {
      setLoading(false);
    }
  };

  const formatPhone = (num) => {
    if (!num) return "";
    let cleaned = num.replace(/^\+234/, "");
    if (cleaned.length === 10) return `+234 ${cleaned.slice(0,3)} ${cleaned.slice(3,6)} ${cleaned.slice(6)}`;
    return num;
  };

  // Determine which PIN to display
  const displayPin = isConfirming ? confirmPin : pin;
  const displayLabel = isConfirming ? "Confirm your PIN" : "Create your PIN";
  const displaySubtext = isConfirming ? "Re-enter your PIN to confirm" : "Create a 4-digit PIN to secure your account";

  return (
    <>
      <LoadingScreen open={loading} message="auth" />

      <div className="fixed inset-0 bg-gradient-to-br from-[#F0F7F2] to-[#E8F3EA] flex flex-col">
        <div className="absolute top-[-100px] left-[-100px] w-80 h-80 bg-[#1A5632]/10 rounded-full blur-3xl" />
        <div className="absolute bottom-[-100px] right-[-100px] w-80 h-80 bg-[#D4A017]/10 rounded-full blur-3xl" />

        {/* Back Button */}
        <button
          onClick={() => navigate("/login")}
          className="absolute top-6 left-6 z-10 w-10 h-10 rounded-full bg-white/80 backdrop-blur-sm shadow-md flex items-center justify-center active:scale-95 transition-all"
        >
          <span className="material-symbols-outlined text-[#1A5632] text-2xl">arrow_back</span>
        </button>

        <div className="flex-1 flex flex-col items-center justify-center px-6">
          <div className="w-full max-w-md">
            
            {/* Logo */}
            <div className="text-center mb-4">
              <div className="w-14 h-14 mx-auto mb-2 rounded-full bg-gradient-to-br from-[#1A5632] to-[#0E3A22] shadow-lg flex items-center justify-center">
                <span className="material-symbols-outlined text-white text-2xl">shield</span>
              </div>
              <h1 className="text-lg font-black text-[#1A5632] tracking-[0.2em]">KIN</h1>
              <p className="text-[#6C757D] text-[10px] mt-0.5">Personal Safety Network</p>
            </div>

            {/* Heading */}
            <div className="text-center mb-4">
              <h2 className="text-xl font-bold text-[#1A1A1A]">{displayLabel}</h2>
              {phone && (
                <p className="text-[#1A5632] font-medium text-xs mt-1">
                  {formatPhone(phone)}
                </p>
              )}
            </div>

            {/* Status indicator */}
            <div className="text-center mb-2">
              <span className={`text-xs font-medium ${isConfirming ? "text-[#D4A017]" : "text-[#1A5632]"}`}>
                {isConfirming ? "Confirm PIN" : "Create PIN"}
              </span>
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

            <p className="text-[#6C757D] text-xs text-center mb-3">{displaySubtext}</p>

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

            {/* Continue Button */}
            <button
              onClick={handleContinue}
              disabled={!canContinue || loading}
              className={`w-full h-12 rounded-xl font-semibold text-base flex items-center justify-center gap-2 mt-2 ${
                !canContinue || loading
                  ? "bg-[#B7D4BF] text-white cursor-not-allowed"
                  : "bg-gradient-to-r from-[#1A5632] to-[#0E3A22] text-white shadow-lg active:scale-95"
              }`}
            >
              {loading ? "Creating PIN..." : "Continue"}
              {!loading && (
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              )}
            </button>

            {/* Bottom text */}
            <div className="text-center mt-4">
              <p className="text-[#6C757D] text-xs">Your PIN protects access to your Kin account.</p>
            </div>
          </div>
        </div>
      </div>

      <style>{`
        @keyframes shake { 0%,100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }
        @keyframes fill { 0% { transform: scale(0); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
        .shake-animation { animation: shake 0.3s ease-in-out; }
        .fill-animation { animation: fill 0.2s ease-out; }
      `}</style>
    </>
  );
}

export default CreatePinScreenV2;
