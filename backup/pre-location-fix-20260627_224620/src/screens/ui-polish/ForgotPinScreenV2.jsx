import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { FaArrowLeft, FaSpinner, FaShieldAlt, FaCheckCircle, FaEnvelope } from "react-icons/fa";

const API_BASE = import.meta.env.VITE_API_URL;

function ForgotPinScreenV2() {
  const navigate = useNavigate();
  const [step, setStep] = useState(1);
  const [email, setEmail] = useState("");
  const [otp, setOtp] = useState("");
  const [newPin, setNewPin] = useState("");
  const [confirmPin, setConfirmPin] = useState("");
  const [resetToken, setResetToken] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
  const [countdown, setCountdown] = useState(0);

  const startCountdown = () => {
    const interval = setInterval(() => {
      setCountdown((prev) => {
        if (prev <= 1) {
          clearInterval(interval);
          return 0;
        }
        return prev - 1;
      });
    }, 1000);
  };

  const sendOtp = async () => {
    if (!email || !email.includes('@')) {
      setError("Enter a valid email address");
      return;
    }

    setLoading(true);
    setError("");
    setSuccess("");

    try {
      const response = await fetch(`${API_BASE}/forgot-pin/send-otp`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email: email.trim() }),
      });

      const data = await response.json();

      if (response.ok) {
        setSuccess(`OTP sent to ${email}`);
        setStep(2);
        setCountdown(60);
        startCountdown();
      } else {
        setError(data.message || "Failed to send OTP");
      }
    } catch (err) {
      setError("Network error. Please try again.");
    } finally {
      setLoading(false);
    }
  };

  const verifyOtp = async () => {
    if (!otp || otp.length !== 6) {
      setError("Enter a valid 6-digit OTP");
      return;
    }

    setLoading(true);
    setError("");
    setSuccess("");

    try {
      const response = await fetch(`${API_BASE}/forgot-pin/verify-otp`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          email: email.trim(),
          otp: otp,
        }),
      });

      const data = await response.json();

      if (response.ok) {
        setSuccess("OTP verified!");
        setResetToken(data.data?.token || data.token);
        setStep(3);
      } else {
        setError(data.message || "Invalid OTP");
      }
    } catch (err) {
      setError("Network error. Please try again.");
    } finally {
      setLoading(false);
    }
  };

  const resetPin = async () => {
    if (newPin.length !== 4) {
      setError("Enter a 4-digit PIN");
      return;
    }
    if (newPin !== confirmPin) {
      setError("PINs do not match");
      return;
    }

    setLoading(true);
    setError("");
    setSuccess("");

    try {
      const response = await fetch(`${API_BASE}/forgot-pin/reset`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          email: email.trim(),
          token: resetToken,
          pin: newPin,
        }),
      });

      const data = await response.json();

      if (response.ok) {
        setSuccess("PIN reset successfully!");
        setTimeout(() => navigate("/login"), 2000);
      } else {
        setError(data.message || "Failed to reset PIN");
      }
    } catch (err) {
      setError("Network error. Please try again.");
    } finally {
      setLoading(false);
    }
  };

  const handleResend = async () => {
    if (countdown > 0) return;
    await sendOtp();
  };

  const getStepTitle = () => {
    switch (step) {
      case 1: return "Reset Your PIN";
      case 2: return "Verify Your Email";
      case 3: return "Create New PIN";
      default: return "Forgot PIN";
    }
  };

  const getStepDescription = () => {
    switch (step) {
      case 1: return "Enter your email to receive a verification code";
      case 2: return "Enter the 6-digit code sent to your email";
      case 3: return "Create a new 4-digit PIN for your account";
      default: return "";
    }
  };

  return (
    <div className="min-h-screen bg-[#F0F7F2] flex items-center justify-center px-6">
      {/* Background orbs */}
      <div className="absolute top-[-100px] left-[-100px] w-80 h-80 bg-[#1A5632]/10 rounded-full blur-3xl" />
      <div className="absolute bottom-[-100px] right-[-100px] w-80 h-80 bg-[#D4A017]/10 rounded-full blur-3xl" />

      <div className="w-full max-w-md relative z-10">
        {/* Back Button */}
        <button
          onClick={() => navigate(-1)}
          className="mb-4 w-10 h-10 rounded-full bg-white/80 backdrop-blur-sm shadow-md flex items-center justify-center active:scale-95 transition-all"
        >
          <FaArrowLeft className="text-[#1A5632] text-xl" />
        </button>

        {/* Logo */}
        <div className="text-center mb-6">
          <div className="w-14 h-14 mx-auto mb-2 rounded-full bg-gradient-to-br from-[#1A5632] to-[#0E3A22] shadow-lg flex items-center justify-center">
            <FaShieldAlt className="text-white text-2xl" />
          </div>
          <h1 className="text-xl font-black text-[#1A5632] tracking-[0.2em]">KIN</h1>
          <p className="text-[#6C757D] text-xs mt-0.5">Personal Safety Network</p>
        </div>

        {/* Card */}
        <div className="bg-white rounded-2xl p-6 shadow-lg border border-[#E9ECEF]">
          <div className="text-center mb-6">
            <h2 className="text-xl font-bold text-[#1A1A1A]">{getStepTitle()}</h2>
            <p className="text-sm text-[#6C757D] mt-1">{getStepDescription()}</p>
          </div>

          {/* Step 1: Enter Email */}
          {step === 1 && (
            <div>
              <div className="mb-4">
                <label className="block text-sm font-medium text-[#6C757D] mb-1">
                  Email Address
                </label>
                <div className="bg-white rounded-xl px-4 py-3 shadow-sm border border-[#E9ECEF] flex items-center gap-3">
                  <FaEnvelope className="text-[#1A5632] text-sm" />
                  <input
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    placeholder="you@email.com"
                    className="flex-1 border-none outline-none bg-transparent text-base"
                    autoFocus
                  />
                </div>
              </div>
            </div>
          )}

          {/* Step 2: Enter OTP */}
          {step === 2 && (
            <div>
              <div className="mb-4">
                <label className="block text-sm font-medium text-[#6C757D] mb-1">
                  Verification Code
                </label>
                <div className="bg-white rounded-xl px-4 py-3 shadow-sm border border-[#E9ECEF] flex items-center gap-3">
                  <input
                    type="text"
                    inputMode="numeric"
                    maxLength={6}
                    value={otp}
                    onChange={(e) => setOtp(e.target.value.replace(/\D/g, ""))}
                    placeholder="123456"
                    className="flex-1 border-none outline-none bg-transparent text-base text-center tracking-[0.5em]"
                    autoFocus
                  />
                </div>
                <p className="text-xs text-[#6C757D] mt-2 text-center">
                  {countdown > 0 ? (
                    `Resend in ${countdown}s`
                  ) : (
                    <button
                      onClick={handleResend}
                      className="text-[#1A5632] font-medium hover:underline"
                    >
                      Resend OTP
                    </button>
                  )}
                </p>
              </div>
            </div>
          )}

          {/* Step 3: Enter New PIN */}
          {step === 3 && (
            <div>
              <div className="mb-3">
                <label className="block text-sm font-medium text-[#6C757D] mb-1">
                  New PIN
                </label>
                <div className="bg-white rounded-xl px-4 py-3 shadow-sm border border-[#E9ECEF]">
                  <input
                    type="password"
                    inputMode="numeric"
                    maxLength={4}
                    value={newPin}
                    onChange={(e) => setNewPin(e.target.value.replace(/\D/g, ""))}
                    placeholder="****"
                    className="w-full border-none outline-none bg-transparent text-2xl text-center tracking-[0.5em]"
                    autoFocus
                  />
                </div>
              </div>
              <div className="mb-4">
                <label className="block text-sm font-medium text-[#6C757D] mb-1">
                  Confirm PIN
                </label>
                <div className="bg-white rounded-xl px-4 py-3 shadow-sm border border-[#E9ECEF]">
                  <input
                    type="password"
                    inputMode="numeric"
                    maxLength={4}
                    value={confirmPin}
                    onChange={(e) => setConfirmPin(e.target.value.replace(/\D/g, ""))}
                    placeholder="****"
                    className="w-full border-none outline-none bg-transparent text-2xl text-center tracking-[0.5em]"
                  />
                </div>
              </div>
            </div>
          )}

          {/* Error / Success Messages */}
          {error && (
            <div className="bg-red-50 rounded-xl p-3 mb-4 border border-red-200">
              <p className="text-red-600 text-sm text-center">{error}</p>
            </div>
          )}
          {success && (
            <div className="bg-green-50 rounded-xl p-3 mb-4 border border-green-200">
              <p className="text-green-600 text-sm text-center">{success}</p>
            </div>
          )}

          {/* Action Button */}
          <button
            onClick={step === 1 ? sendOtp : step === 2 ? verifyOtp : resetPin}
            disabled={loading}
            className="w-full h-12 rounded-xl bg-gradient-to-r from-[#1A5632] to-[#0E3A22] text-white font-semibold text-base shadow-lg shadow-[#1A5632]/20 hover:opacity-90 active:scale-95 transition-all disabled:opacity-50 flex items-center justify-center gap-2"
          >
            {loading ? (
              <>
                <FaSpinner className="animate-spin" />
                Processing...
              </>
            ) : step === 1 ? (
              "Send OTP"
            ) : step === 2 ? (
              "Verify OTP"
            ) : (
              "Reset PIN"
            )}
          </button>
        </div>

        {/* Version Info */}
        <p className="text-center text-[10px] text-[#6C757D] mt-6">
          KIN v1.0.0 • Protecting what matters
        </p>
      </div>
    </div>
  );
}

export default ForgotPinScreenV2;
