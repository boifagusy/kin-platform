import { updateProfile, updateStep, STEPS } from "../../services/onboardingDraftService";
import { useState } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import LoadingScreen from "../../components/ui/LoadingScreen";

const API_BASE = import.meta.env.VITE_API_URL || "http://127.0.0.1:8000";

function UserDetailsScreenV2() {
  const navigate = useNavigate();
  const location = useLocation();
  const phone = location.state?.phone || "";

  const [fullName, setFullName] = useState("");
  const [email, setEmail] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  // Email validation function
  const isValidEmail = (email) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  };

  // ✅ Name required (>= 3 chars) + Valid email required
  const canContinue = fullName.trim().length >= 3 && isValidEmail(email);

  async function handleContinue() {
    if (loading || !canContinue) return;

    try {
      setLoading(true);
    // Save draft
    updateProfile({ name: fullName, email: email });
    updateStep(STEPS.CHECKIN);
      setError("");

      const response = await fetch(`${API_BASE}/api/v1/auth/user-details`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          phone,
          full_name: fullName.trim(),
          email: email.trim(),
        }),
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.error || data.message || "Unable to save details");
      }

      navigate("/trusted-contact", {
        state: { phone },
      });
    } catch (error) {
      console.error("ERROR:", error);
      setError(error.message || "Unable to save details");
    } finally {
      setLoading(false);
    }
  }

  const formatPhone = (num) => {
    if (!num) return "";
    let cleaned = num.replace(/^\+234/, "");
    if (cleaned.length === 10) return `+234 ${cleaned.slice(0,3)} ${cleaned.slice(3,6)} ${cleaned.slice(6)}`;
    return num;
  };

  // Check if email is valid for styling
  const isEmailValid = email.length > 0 && isValidEmail(email);
  const isEmailInvalid = email.length > 0 && !isValidEmail(email);

  return (
    <>
      <LoadingScreen open={loading} message="auth" />

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
            <div className="text-center mb-4">
              <div className="w-14 h-14 mx-auto mb-2 rounded-full bg-gradient-to-br from-[#1A5632] to-[#0E3A22] shadow-lg flex items-center justify-center">
                <span className="material-symbols-outlined text-white text-2xl">shield</span>
              </div>
              <h1 className="text-lg font-black text-[#1A5632] tracking-[0.2em]">KIN</h1>
              <p className="text-[#6C757D] text-[10px] mt-0.5">Personal Safety Network</p>
            </div>

            {/* Progress Steps */}
            <div className="flex justify-center gap-2 mb-6">
              {[0, 1, 2, 3].map((index) => (
                <div
                  key={index}
                  className={`w-2 h-2 rounded-full ${
                    index === 0 ? "bg-[#1A5632]" :
                    index === 1 ? "bg-[#D4A017]" :
                    "bg-[#D1D5DB]"
                  }`}
                />
              ))}
            </div>

            {/* Heading */}
            <div className="text-center mb-2">
              <h2 className="text-xl font-bold text-[#1A1A1A]">Tell us about yourself</h2>
            </div>

            {/* Phone Display */}
            <div className="text-center mb-4">
              <p className="text-[#1A5632] font-medium text-xs">
                📱 {formatPhone(phone)}
              </p>
            </div>

            <p className="text-[#6C757D] text-xs text-center mb-6">
              This helps us recover your account if you lose access.
            </p>

            {/* Full Name Input */}
            <div className="bg-white rounded-xl p-4 shadow-sm border border-[#E9ECEF] flex items-center gap-3 mb-3">
              <span className="material-symbols-outlined text-[#1A5632] text-xl">person</span>
              <input
                type="text"
                placeholder="Full Name"
                value={fullName}
                onChange={(e) => setFullName(e.target.value)}
                className="flex-1 border-none outline-none bg-transparent text-base placeholder:text-[#6C757D]"
                autoFocus
              />
            </div>

            {/* Email Input - With validation feedback */}
            <div className={`bg-white rounded-xl p-4 shadow-sm border flex items-center gap-3 mb-1 ${
              isEmailInvalid ? "border-red-500" : "border-[#E9ECEF]"
            }`}>
              <span className={`material-symbols-outlined text-xl ${
                isEmailInvalid ? "text-red-500" : "text-[#1A5632]"
              }`}>email</span>
              <input
                type="email"
                placeholder="Email Address *"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                className="flex-1 border-none outline-none bg-transparent text-base placeholder:text-[#6C757D]"
              />
              {isEmailValid && (
                <span className="material-symbols-outlined text-green-500 text-lg">check_circle</span>
              )}
              {isEmailInvalid && (
                <span className="material-symbols-outlined text-red-500 text-lg">error</span>
              )}
            </div>

            {/* Email validation feedback */}
            {isEmailInvalid && (
              <p className="text-red-500 text-[10px] text-left pl-1 mb-3">
                Please enter a valid email address
              </p>
            )}
            {email.length > 0 && isEmailValid && (
              <p className="text-green-600 text-[10px] text-left pl-1 mb-3">
                ✓ Valid email address
              </p>
            )}
            {email.length === 0 && (
              <p className="text-[#6C757D] text-[10px] text-left pl-1 mb-3">
                * Required for password recovery
              </p>
            )}

            {error && (
              <p className="text-red-500 text-xs text-center mb-3">{error}</p>
            )}

            {/* Trust Message */}
            <div className="flex items-center justify-center gap-2 mb-6">
              <span className="material-symbols-outlined text-[#16A34A] text-sm">verified</span>
              <span className="text-[#6C757D] text-xs">Your information is encrypted and never shared.</span>
            </div>

            {/* Continue Button */}
            <button
              onClick={handleContinue}
              disabled={!canContinue || loading}
              className={`w-full h-12 rounded-xl font-semibold text-base flex items-center justify-center gap-2 ${
                !canContinue || loading
                  ? "bg-[#B7D4BF] text-white cursor-not-allowed"
                  : "bg-gradient-to-r from-[#1A5632] to-[#0E3A22] text-white shadow-lg active:scale-95"
              }`}
            >
              {loading ? "Saving..." : "Continue"}
              {!loading && (
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              )}
            </button>
          </div>
        </div>
      </div>

      <style>{`
        @keyframes shake { 0%,100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }
        .shake-animation { animation: shake 0.3s ease-in-out; }
      `}</style>
    </>
  );
}

export default UserDetailsScreenV2;
