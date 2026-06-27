import { useState, useEffect } from "react";
import { useNavigate, useLocation } from "react-router-dom";

const API_BASE = import.meta.env.VITE_API_URL || "http://127.0.0.1:8000";

function LoginPinScreenV2() {
  const navigate = useNavigate();
  const location = useLocation();
  const phone = location.state?.phone || localStorage.getItem("kin_phone") || "";

  useEffect(() => {
    if (!phone) navigate("/login", { replace: true });
  }, [phone, navigate]);

  const [pin, setPin] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [shake, setShake] = useState(false);

  const isValid = pin.length === 4;

  const handleNumberClick = (num) => {
    if (loading) return;
    if (pin.length < 4) {
      setPin(prev => prev + num);
      setError("");
    }
  };

  const handleDeleteClick = () => {
    if (loading) return;
    if (pin.length > 0) {
      // FIX: Use functional update to ensure state updates
      setPin(prev => prev.slice(0, -1));
      setError("");
    }
  };

  const handleClearClick = () => {
    if (loading) return;
    setPin("");
    setError("");
  };

  const handleLogin = async () => {
    if (loading || !isValid) return;
    setLoading(true);
    setError("");
    try {
      const res = await fetch(`${API_BASE}/api/v1/auth/login-pin`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ phone, pin }),
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.message || "Invalid PIN");
      localStorage.setItem("kin_phone", phone);
      if (data.onboarding_completed) navigate("/dashboard", { state: { phone }, replace: true });
      else navigate("/user-details", { state: { phone } });
    } catch (err) {
      setError(err.message);
      setPin("");
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

  return (
    <div className="fixed inset-0 bg-gradient-to-br from-[#F0F7F2] to-[#E8F3EA] flex flex-col">
      <div className="absolute top-[-100px] left-[-100px] w-80 h-80 bg-[#1A5632]/10 rounded-full blur-3xl" />
      <div className="absolute bottom-[-100px] right-[-100px] w-80 h-80 bg-[#D4A017]/10 rounded-full blur-3xl" />

      <button onClick={() => navigate("/login")} className="absolute top-6 left-6 z-10 w-10 h-10 rounded-full bg-white/80 backdrop-blur-sm shadow-md flex items-center justify-center">
        <span className="material-symbols-outlined text-[#1A5632] text-2xl">arrow_back</span>
      </button>

      <div className="flex-1 flex flex-col items-center justify-center px-6">
        <div className="w-full max-w-md">
          <div className="text-center mb-4">
            <div className="w-14 h-14 mx-auto mb-2 rounded-full bg-gradient-to-br from-[#1A5632] to-[#0E3A22] shadow-lg flex items-center justify-center">
              <span className="material-symbols-outlined text-white text-2xl">shield</span>
            </div>
            <h1 className="text-lg font-black text-[#1A5632] tracking-[0.2em]">KIN</h1>
            <p className="text-[#6C757D] text-[10px] mt-0.5">Personal Safety Network</p>
          </div>

          <div className="text-center mb-4">
            <h2 className="text-xl font-bold text-[#1A1A1A]">Welcome Back</h2>
            <p className="text-[#1A5632] font-medium text-xs mt-1">{formatPhone(phone)}</p>
          </div>

          <div className="flex justify-center gap-3 mb-4">
            {[0,1,2,3].map((i) => (
              <div key={i} className={`w-12 h-12 rounded-xl bg-white shadow-md border-2 flex items-center justify-center ${pin.length > i ? "border-[#1A5632] bg-[#1A5632]/5" : "border-[#E9ECEF]"} ${shake ? "shake-animation" : ""}`}>
                {pin.length > i && <div className="w-3 h-3 rounded-full bg-[#1A5632] fill-animation" />}
              </div>
            ))}
          </div>
          <p className="text-[#6C757D] text-xs text-center mb-3">Enter your 4-digit PIN</p>
          {error && <p className="text-red-500 text-xs text-center mb-3">{error}</p>}

          <div className="grid grid-cols-3 gap-3 mb-3 relative z-50">
            <button onClick={() => handleNumberClick("1")} className="h-14 rounded-xl bg-white shadow-md border text-2xl font-semibold active:scale-95 transition-all">1</button>
            <button onClick={() => handleNumberClick("2")} className="h-14 rounded-xl bg-white shadow-md border text-2xl font-semibold active:scale-95 transition-all">2</button>
            <button onClick={() => handleNumberClick("3")} className="h-14 rounded-xl bg-white shadow-md border text-2xl font-semibold active:scale-95 transition-all">3</button>
            <button onClick={() => handleNumberClick("4")} className="h-14 rounded-xl bg-white shadow-md border text-2xl font-semibold active:scale-95 transition-all">4</button>
            <button onClick={() => handleNumberClick("5")} className="h-14 rounded-xl bg-white shadow-md border text-2xl font-semibold active:scale-95 transition-all">5</button>
            <button onClick={() => handleNumberClick("6")} className="h-14 rounded-xl bg-white shadow-md border text-2xl font-semibold active:scale-95 transition-all">6</button>
            <button onClick={() => handleNumberClick("7")} className="h-14 rounded-xl bg-white shadow-md border text-2xl font-semibold active:scale-95 transition-all">7</button>
            <button onClick={() => handleNumberClick("8")} className="h-14 rounded-xl bg-white shadow-md border text-2xl font-semibold active:scale-95 transition-all">8</button>
            <button onClick={() => handleNumberClick("9")} className="h-14 rounded-xl bg-white shadow-md border text-2xl font-semibold active:scale-95 transition-all">9</button>
            <button onClick={handleClearClick} className="h-14 rounded-xl bg-white shadow-md border text-sm font-medium text-[#6C757D] active:scale-95 transition-all">Clear</button>
            <button onClick={() => handleNumberClick("0")} className="h-14 rounded-xl bg-white shadow-md border text-2xl font-semibold active:scale-95 transition-all">0</button>
            <button onClick={handleDeleteClick} className="h-14 rounded-xl bg-white shadow-md border text-sm font-medium text-red-600 active:scale-95 transition-all relative z-50">Del</button>
          </div>

          <button onClick={handleLogin} disabled={!isValid || loading} className={`w-full h-12 rounded-xl font-semibold text-base flex items-center justify-center gap-2 mt-2 ${!isValid || loading ? "bg-[#B7D4BF] text-white" : "bg-gradient-to-r from-[#1A5632] to-[#0E3A22] text-white shadow-lg active:scale-95"}`}>
            {loading ? "Signing you in..." : "Continue"}
            {!loading && <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>}
          </button>

          <div className="text-center mt-4">
            <button onClick={() => navigate("/forgot-pin")} className="text-xs text-[#1A5632] font-semibold hover:underline">Forgot PIN?</button>
          </div>
        </div>
      </div>

      <style>{`
        @keyframes shake { 0%,100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }
        @keyframes fill { 0% { transform: scale(0); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
        .shake-animation { animation: shake 0.3s ease-in-out; }
        .fill-animation { animation: fill 0.2s ease-out; }
      `}</style>
    </div>
  );
}

export default LoginPinScreenV2;
