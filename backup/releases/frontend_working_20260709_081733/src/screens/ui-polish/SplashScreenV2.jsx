import { useEffect } from "react";
import { useNavigate } from "react-router-dom";

function SplashScreenV2() {
  const navigate = useNavigate();

  useEffect(() => {
    // Simulate loading auth state
    const timer = setTimeout(() => {
      // Check for existing auth token
      const token = localStorage.getItem("kin_token");
      
      if (token) {
        navigate("/dashboard");
      } else {
        navigate("/");
      }
    }, 1800); // 1.8 seconds

    return () => clearTimeout(timer);
  }, [navigate]);

  return (
    <div className="min-h-screen bg-gradient-to-br from-[#F0F7F2] to-[#E8F3EA] flex flex-col items-center justify-center">
      {/* Background blur orbs */}
      <div className="absolute top-[-100px] left-[-100px] w-80 h-80 bg-[#1A5632]/10 rounded-full blur-3xl" />
      <div className="absolute bottom-[-100px] right-[-100px] w-80 h-80 bg-[#D4A017]/10 rounded-full blur-3xl" />
      
      {/* Content */}
      <div className="relative z-10 text-center animate-fade-in">
        {/* Logo Animation Container */}
        <div className="relative w-32 h-32 mx-auto mb-4">
          {/* Pulsing ring */}
          <div className="absolute inset-0 rounded-full border-4 border-[#1A5632]/20 animate-ping-slow" />
          <div className="absolute inset-2 rounded-full border-4 border-[#1A5632]/40 animate-pulse" />
          
          {/* Center logo */}
          <div className="absolute inset-4 rounded-full bg-gradient-to-br from-[#1A5632] to-[#0E3A22] shadow-xl flex items-center justify-center">
            <span className="material-symbols-outlined text-white text-5xl" style={{ fontVariationSettings: "'FILL' 1" }}>
              security
            </span>
          </div>
        </div>
        
        {/* Brand Name */}
        <h1 className="text-3xl font-black text-[#1A5632] tracking-[0.3em]">
          KIN
        </h1>
        
        {/* Tagline */}
        <p className="text-[#6C757D] text-sm mt-2 tracking-wide">
          Personal Safety Network
        </p>
        
        {/* Loading indicator */}
        <div className="mt-8 flex justify-center gap-1">
          <div className="w-2 h-2 bg-[#1A5632] rounded-full animate-bounce-delayed-1" />
          <div className="w-2 h-2 bg-[#1A5632] rounded-full animate-bounce-delayed-2" />
          <div className="w-2 h-2 bg-[#1A5632] rounded-full animate-bounce-delayed-3" />
        </div>
      </div>

      {/* Animations */}
      <style>{`
        @keyframes fade-in {
          from { opacity: 0; transform: scale(0.95); }
          to { opacity: 1; transform: scale(1); }
        }
        @keyframes ping-slow {
          0% { transform: scale(1); opacity: 0.4; }
          100% { transform: scale(1.5); opacity: 0; }
        }
        @keyframes bounce-delayed-1 {
          0%, 100% { transform: translateY(0); opacity: 0.3; }
          50% { transform: translateY(-8px); opacity: 1; }
        }
        @keyframes bounce-delayed-2 {
          0%, 100% { transform: translateY(0); opacity: 0.3; }
          50% { transform: translateY(-8px); opacity: 1; }
        }
        @keyframes bounce-delayed-3 {
          0%, 100% { transform: translateY(0); opacity: 0.3; }
          50% { transform: translateY(-8px); opacity: 1; }
        }
        .animate-fade-in {
          animation: fade-in 0.6s ease-out;
        }
        .animate-ping-slow {
          animation: ping-slow 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
        }
        .animate-bounce-delayed-1 {
          animation: bounce-delayed-1 1s ease-in-out infinite;
          animation-delay: 0s;
        }
        .animate-bounce-delayed-2 {
          animation: bounce-delayed-2 1s ease-in-out infinite;
          animation-delay: 0.2s;
        }
        .animate-bounce-delayed-3 {
          animation: bounce-delayed-3 1s ease-in-out infinite;
          animation-delay: 0.4s;
        }
      `}</style>
    </div>
  );
}

export default SplashScreenV2;
