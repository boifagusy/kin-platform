import React, { useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { FaShieldAlt, FaUsers, FaMapMarkerAlt, FaClock, FaBell } from "react-icons/fa";
import { hasDraft, getDraft, getStep, formatTimeAgo, STEPS } from "../../services/onboardingDraftService";

const STEP_NAMES = {
  'welcome': 'Welcome',
  'phone': 'Phone Number',
  'pin': 'Create PIN',
  'user-details': 'User Details',
  'checkin': 'Check-In Settings',
  'duress': 'Duress PIN',
  'dashboard': 'Almost Done',
  'complete': 'Complete',
};

function WelcomeScreenV3() {
  const navigate = useNavigate();
  const draft = getDraft();
  const draftStep = getStep();
  const timeAgo = formatTimeAgo(draft?.updated_at);
  const hasExistingDraft = hasDraft();

  const handleGetStarted = () => {
    navigate("/login");
  };

  const handleLogIn = () => {
    navigate("/login");
  };

  const handleContinueSetup = () => {
    navigate("/continue-setup");
  };

  return (
    <div className="min-h-screen bg-[#F0F7F2] flex items-center justify-center px-6">
      {/* Background orbs */}
      <div className="absolute top-[-100px] left-[-100px] w-80 h-80 bg-[#1A5632]/10 rounded-full blur-3xl" />
      <div className="absolute bottom-[-100px] right-[-100px] w-80 h-80 bg-[#D4A017]/10 rounded-full blur-3xl" />

      <div className="w-full max-w-md relative z-10">
        {/* Logo */}
        <div className="text-center mb-4">
          <div className="w-16 h-16 mx-auto mb-3 rounded-full bg-gradient-to-br from-[#1A5632] to-[#0E3A22] shadow-lg flex items-center justify-center">
            <FaShieldAlt className="text-white text-3xl" />
          </div>
          <h1 className="text-2xl font-black text-[#1A5632] tracking-[0.15em]">KIN</h1>
          <p className="text-[#6C757D] text-xs mt-1">Personal Safety Network</p>
        </div>

        {/* Headline */}
        <div className="text-center mb-3">
          <h2 className="text-xl font-bold text-[#1A1A1A] leading-tight">
            Stay connected to the people
            <br />
            who matter most.
          </h2>
          <p className="text-[#6C757D] text-sm mt-2 leading-relaxed">
            Trusted contacts, daily safety check-ins, and emergency
            protection when you need it.
          </p>
        </div>

        {/* Feature Cards - 2x2 Grid */}
        <div className="grid grid-cols-2 gap-2.5 mb-4">
          <div className="bg-white rounded-xl p-3 shadow-sm border border-[#E9ECEF] flex items-center gap-2.5">
            <div className="w-8 h-8 rounded-full bg-[#E8F3EA] flex items-center justify-center">
              <FaUsers className="text-[#1A5632] text-sm" />
            </div>
            <span className="text-xs font-medium text-[#1A1A1A]">Trusted Contact</span>
          </div>
          <div className="bg-white rounded-xl p-3 shadow-sm border border-[#E9ECEF] flex items-center gap-2.5">
            <div className="w-8 h-8 rounded-full bg-[#E8F3EA] flex items-center justify-center">
              <FaMapMarkerAlt className="text-[#1A5632] text-sm" />
            </div>
            <span className="text-xs font-medium text-[#1A1A1A]">Live Location</span>
          </div>
          <div className="bg-white rounded-xl p-3 shadow-sm border border-[#E9ECEF] flex items-center gap-2.5">
            <div className="w-8 h-8 rounded-full bg-[#E8F3EA] flex items-center justify-center">
              <FaClock className="text-[#1A5632] text-sm" />
            </div>
            <span className="text-xs font-medium text-[#1A1A1A]">Daily Check-In</span>
          </div>
          <div className="bg-white rounded-xl p-3 shadow-sm border border-[#E9ECEF] flex items-center gap-2.5">
            <div className="w-8 h-8 rounded-full bg-[#E8F3EA] flex items-center justify-center">
              <FaBell className="text-[#1A5632] text-sm" />
            </div>
            <span className="text-xs font-medium text-[#1A1A1A]">SOS Alerts</span>
          </div>
        </div>

        {/* Continue Setup Banner (if draft exists) */}
        {hasExistingDraft && (
          <div className="bg-white rounded-xl p-3 shadow-sm border border-[#D4A017] mb-3">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-xs font-medium text-[#D4A017]">Continue Setup</p>
                <p className="text-xs text-[#6C757D]">
                  Last step: {STEP_NAMES[draftStep] || 'Setup'}
                  <span className="text-[10px] text-[#6C757D] ml-2">• {timeAgo}</span>
                </p>
              </div>
              <button
                onClick={handleContinueSetup}
                className="px-4 py-2 rounded-lg bg-[#1A5632] text-white text-xs font-medium hover:opacity-90 active:scale-95 transition-all"
              >
                Resume
              </button>
            </div>
          </div>
        )}

        {/* Primary CTA */}
        <button
          onClick={handleGetStarted}
          className="w-full h-12 rounded-xl bg-gradient-to-r from-[#1A5632] to-[#0E3A22] text-white font-semibold text-base shadow-lg shadow-[#1A5632]/20 hover:opacity-90 active:scale-95 transition-all"
        >
          Build My Safety Network
        </button>

        {/* Log In */}
        <div className="text-center mt-3">
          <button
            onClick={handleLogIn}
            className="text-sm text-[#6C757D] hover:text-[#1A5632] transition-colors"
          >
            Already have an account? <span className="font-semibold">Log In</span>
          </button>
        </div>

        {/* Version Info */}
        <p className="text-center text-[10px] text-[#6C757D] mt-5">
          KIN v1.0.0 • Protecting what matters
        </p>
      </div>
    </div>
  );
}

export default WelcomeScreenV3;
