import { saveAuth } from "../../utils/auth";
import { useState, useEffect } from "react";
import { useNavigate, useLocation } from "react-router-dom";

const API_BASE = import.meta.env.VITE_API_URL;

function LoginPinScreenV2() {
  const navigate = useNavigate();
  const location = useLocation();
  const phone = location.state?.phone || localStorage.getItem("kin_phone") || "";
  const storedName = localStorage.getItem("kin_name") || "";

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
    setPin(prev => prev.slice(0, -1));
    setError("");
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
      const res = await fetch(`${API_BASE}/auth/login-pin`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ phone, pin }),
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.message || "Invalid PIN");
      if (data.token) saveAuth(phone, data.token);
      if (data.name) localStorage.setItem("kin_name", data.name);
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

  const getInitials = (name) => {
    if (!name) return "U";
    return name.split(" ").map(n => n[0]).join("").toUpperCase().slice(0, 2);
  };

  const getGreeting = () => {
    const hour = new Date().getHours();
    if (hour < 12) return "Good Morning";
    if (hour < 17) return "Good Afternoon";
    return "Good Evening";
  };

  return (
    <div className="fixed inset-0 bg-gradient-to-br from-[#F0F7F2] to-[#E8F3EA] flex flex-col">
      <div className="absolute top-[-100px] left-[-100px] w-80 h-80 bg-[#1A5632]/10 rounded-full blur-3xl" />
      <div className="absolute bottom-[-100px] right-[-100px] w-80 h-80 bg-[#D4A017]/10 rounded-full blur-3xl" />

      <div className="flex-1 flex flex-col items-center justify-center px-6">
        <div className="w-full max-w-md">

          {/* KIN Logo */}
          <div className="text-center mb-6">
            <div className="w-12 h-12 mx-auto mb-1 rounded-full bg-gradient-to-br from-[#1A5632] to-[#0E3A22] shadow-lg flex items-center justify-center">
              <span className="material-symbols-outlined text-white text-xl">shield</span>
            </div>
            <p className="text-lg font-black text-[#1A5632] tracking-[0.2em]">KIN</p>
          </div>

          {/* User Avatar + Welcome */}
          <div className="text-center mb-6">
            <div className="w-20 h-20 mx-auto mb-3 rounded-full bg-gradient-to-br from-[#1A5632] to-[#3A7D44] shadow-lg flex items-center justify-center">
              <span className="text-2xl font-bold text-white">{getInitials(storedName)}</span>
            </div>
            <p className="text-sm text-[#6C757D]">{getGreeting()},</p>
            <h2 className="text-2xl font-bold text-[#1A1A1A] mt-0.5">
              {storedName || "Welcome Back"}
            </h2>
            <p className="text-[#1A5632] font-medium text-xs mt-1">{formatPhone(phone)}</p>
          </div>

          {/* PIN dots */}
          <div className="flex justify-center gap-3 mb-2">
            {[0,1,2,3].map((i) => (
              <div key={i} className={`w-12 h-12 rounded-xl bg-white shadow-md border-2 flex items-center justify-center transition-all ${pin.length > i ? "border-[#1A5632] bg-[#1A5632]/5" : "border-[#E9ECEF]"} ${shake ? "shake-animation" : ""}`}>
                {pin.length > i && <div className="w-3 h-3 rounded-full bg-[#1A5632] fill-animation" />}
              </div>
            ))}
          </div>
          <p className="text-[#6C757D] text-xs text-center mb-2">Enter your 4-digit PIN</p>
          {error && <p className="text-red-500 text-xs text-center mb-2 font-medium">{error}</p>}

          {/* PIN pad */}
          <div className="grid grid-cols-3 gap-3 mb-3">
            {["1","2","3","4","5","6","7","8","9"].map(num => (
              <button key={num} onClick={() => handleNumberClick(num)}
                className="h-14 rounded-xl bg-white shadow-md border text-2xl font-semibold active:scale-95 transition-all">
                {num}
              </button>
            ))}
            <button onClick={handleClearClick}
              className="h-14 rounded-xl bg-white shadow-md border text-sm font-medium text-[#6C757D] active:scale-95 transition-all">
              Clear
            </button>
            <button onClick={() => handleNumberClick("0")}
              className="h-14 rounded-xl bg-white shadow-md border text-2xl font-semibold active:scale-95 transition-all">
              0
            </button>
            <button onClick={handleDeleteClick}
              className="h-14 rounded-xl bg-white shadow-md border text-sm font-medium text-red-600 active:scale-95 transition-all">
              Del
            </button>
          </div>

          {/* Login button */}
          <button onClick={handleLogin} disabled={!isValid || loading}
            className={`w-full h-12 rounded-xl font-semibold text-base flex items-center justify-center gap-2 ${!isValid || loading ? "bg-[#B7D4BF] text-white" : "bg-gradient-to-r from-[#1A5632] to-[#0E3A22] text-white shadow-lg active:scale-95 transition-all"}`}>
            {loading ? "Signing you in..." : "Sign In"}
            {!loading && <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>}
          </button>

          {/* Footer links */}
          <div className="flex items-center justify-center gap-4 mt-5">
            <button onClick={() => { localStorage.removeItem("kin_phone"); localStorage.removeItem("kin_name"); navigate("/login"); }}
              className="text-xs text-[#6C757D] hover:text-[#1A5632] transition-colors">
              Switch Account
            </button>
            <span className="text-[#E9ECEF]">|</span>
            <button onClick={() => navigate("/forgot-pin")}
              className="text-xs text-[#1A5632] font-semibold hover:underline">
              Forgot PIN?
            </button>
          </div>
        </div>
      </div>

      <style>{`
        @keyframes shake { 0%,100%{transform:translateX(0)} 25%{transform:translateX(-5px)} 75%{transform:translateX(5px)} }
        @keyframes fill { 0%{transform:scale(0);opacity:0} 100%{transform:scale(1);opacity:1} }
        .shake-animation { animation: shake 0.3s ease-in-out; }
        .fill-animation { animation: fill 0.2s ease-out; }
      `}</style>
    </div>
  );
}

export default LoginPinScreenV2;
