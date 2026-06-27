import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { FaShieldAlt, FaUsers, FaMapMarkerAlt, FaClock, FaBell, FaChevronRight } from "react-icons/fa";
import { hasDraft } from "../../services/onboardingDraftService";

function WelcomeScreenV3() {
  const navigate = useNavigate();
  const [hasExistingDraft, setHasExistingDraft] = useState(false);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    try {
      const draftExists = hasDraft();
      setHasExistingDraft(draftExists);
    } catch (error) {
      console.error('Failed to check draft:', error);
      setHasExistingDraft(false);
    } finally {
      setIsLoading(false);
    }
  }, []);

  const handleGetStarted = () => {
    try {
      // ✅ FIXED: Navigate to /phone (which exists in router)
      navigate("/login");
    } catch (error) {
      console.error('Navigation failed:', error);
    }
  };

  const handleLogIn = () => {
    try {
      navigate("/login");
    } catch (error) {
      console.error('Navigation failed:', error);
    }
  };

  const handleContinueSetup = () => {
    try {
      navigate("/continue-setup");
    } catch (error) {
      console.error('Navigation failed:', error);
    }
  };

  if (isLoading) {
    return (
      <div className="h-screen w-screen bg-[#F0F7F2] flex items-center justify-center">
        <div className="w-12 h-12 border-4 border-[#1A5632] border-t-transparent rounded-full animate-spin" />
      </div>
    );
  }

  return (
    <div className="min-h-screen w-full bg-[#F0F7F2] flex items-center justify-center px-4 py-8 overflow-y-auto">
      <div className="fixed top-[-80px] left-[-80px] w-60 h-60 bg-[#1A5632]/10 rounded-full blur-3xl pointer-events-none" />
      <div className="fixed bottom-[-80px] right-[-80px] w-60 h-60 bg-[#D4A017]/10 rounded-full blur-3xl pointer-events-none" />

      <div className="w-full max-w-md relative z-10 flex flex-col items-center justify-center gap-4 py-6">
        
        {/* Logo */}
        <div className="text-center" role="banner">
          <div 
            className="w-20 h-20 mx-auto rounded-full bg-gradient-to-br from-[#1A5632] to-[#0E3A22] shadow-lg flex items-center justify-center"
            aria-label="KIN Shield Logo"
          >
            <FaShieldAlt className="text-white text-3xl" aria-hidden="true" />
          </div>
          <h1 className="text-3xl font-black text-[#1A5632] tracking-[0.15em] mt-2">KIN</h1>
          <p className="text-[#6C757D] text-xs font-medium">Personal Safety Network</p>
        </div>

        {/* Description */}
        <div className="text-center max-w-sm">
          <p className="text-[#4A555E] text-sm leading-relaxed font-medium">
            Stay connected to the people who matter most.
          </p>
          <p className="text-[#6C757D] text-xs mt-1">
            Trusted contacts, daily check-ins & emergency protection.
          </p>
        </div>

        {/* Features */}
        <div className="grid grid-cols-2 gap-3 w-full" role="list">
          {[
            { icon: FaUsers, label: "Trusted Contacts", description: "Your safety network" },
            { icon: FaMapMarkerAlt, label: "Live Location", description: "Real-time tracking" },
            { icon: FaClock, label: "Daily Check-In", description: "Regular safety checks" },
            { icon: FaBell, label: "SOS Alerts", description: "Emergency notifications" },
          ].map((item, idx) => (
            <div 
              key={idx} 
              className="bg-white/80 backdrop-blur-sm rounded-xl py-3 px-2 text-center border border-white/60 shadow-sm hover:shadow-md transition-shadow"
              role="listitem"
            >
              <item.icon className="text-[#1A5632] text-lg mx-auto mb-1" aria-hidden="true" />
              <span className="text-[#1A5632] text-xs font-semibold block">{item.label}</span>
              <span className="text-[#6C757D] text-[9px] block">{item.description}</span>
            </div>
          ))}
        </div>

        {/* Buttons */}
        <div className="space-y-2 w-full mt-2">
          <button
            onClick={handleGetStarted}
            className="w-full h-12 rounded-xl bg-gradient-to-r from-[#1A5632] to-[#0E3A22] text-white font-semibold text-sm shadow-lg shadow-[#1A5632]/30 hover:shadow-xl hover:opacity-95 active:scale-[0.98] transition-all duration-200 flex items-center justify-center gap-2"
            aria-label="Start building your safety network"
          >
            Build My Safety Network
            <FaChevronRight className="text-xs" aria-hidden="true" />
          </button>
          
          {hasExistingDraft && (
            <button
              onClick={handleContinueSetup}
              className="w-full h-11 rounded-xl bg-[#1A5632]/10 text-[#1A5632] font-medium text-sm hover:bg-[#1A5632]/20 active:scale-[0.98] transition-all duration-200 border border-[#1A5632]/20"
              aria-label="Continue your setup"
            >
              Continue Setup
            </button>
          )}

          <div className="text-center pt-1">
            <button
              onClick={handleLogIn}
              className="text-[#6C757D] text-xs hover:text-[#1A5632] transition-colors duration-200 font-medium"
              aria-label="Log in to your account"
            >
              Already have an account? <span className="text-[#1A5632] font-semibold hover:underline">Log In</span>
            </button>
          </div>
        </div>

        {/* Footer */}
        <div className="text-center mt-2">
          <p className="text-[#9CA3AF] text-[10px] font-medium">
            KIN v1.0.0 • Protecting what matters
          </p>
          <p className="text-[#9CA3AF] text-[8px] mt-0.5">
            By continuing you agree to our Terms of Service
          </p>
        </div>
      </div>
    </div>
  );
}

export default WelcomeScreenV3;
