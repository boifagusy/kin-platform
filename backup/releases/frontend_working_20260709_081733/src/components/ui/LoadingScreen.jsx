function LoadingScreen({ open = false, message = "default" }) {
  // IMPORTANT: Return null when not open - prevents blocking clicks
  if (!open) return null;

  const messages = {
    auth: "Securing your account...",
    network: "Preparing your safety network...",
    emergency: "Connecting emergency services...",
    location: "Verifying your location...",
    contacts: "Loading your trusted circle...",
    default: "Almost ready..."
  };

  const displayMessage = messages[message] || messages.default;

  return (
    <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-md flex items-center justify-center">
      <div className="bg-white rounded-2xl p-8 shadow-2xl max-w-xs w-full mx-4">
        <div className="relative w-20 h-20 mx-auto mb-4">
          <div className="absolute inset-0 rounded-full border-4 border-[#1A5632]/20 animate-ping" />
          <div className="absolute inset-0 rounded-full bg-gradient-to-br from-[#1A5632] to-[#0E3A22] shadow-xl flex items-center justify-center">
            <span className="material-symbols-outlined text-white text-3xl animate-pulse">shield</span>
          </div>
        </div>
        
        <h2 className="text-lg font-black text-[#1A5632] tracking-[0.2em] text-center">KIN</h2>
        <p className="text-[#6C757D] text-sm text-center mt-3">{displayMessage}</p>
        
        <div className="mt-4 flex justify-center gap-1">
          <div className="w-1.5 h-1.5 bg-[#1A5632] rounded-full animate-bounce" style={{ animationDelay: "0s" }} />
          <div className="w-1.5 h-1.5 bg-[#1A5632] rounded-full animate-bounce" style={{ animationDelay: "0.2s" }} />
          <div className="w-1.5 h-1.5 bg-[#1A5632] rounded-full animate-bounce" style={{ animationDelay: "0.4s" }} />
        </div>
      </div>

      <style>{`
        @keyframes ping {
          0% { transform: scale(1); opacity: 0.4; }
          100% { transform: scale(1.4); opacity: 0; }
        }
        @keyframes bounce {
          0%, 100% { transform: translateY(0); opacity: 0.3; }
          50% { transform: translateY(-6px); opacity: 1; }
        }
        .animate-ping {
          animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
        }
        .animate-bounce {
          animation: bounce 1s ease-in-out infinite;
        }
      `}</style>
    </div>
  );
}

export default LoadingScreen;
