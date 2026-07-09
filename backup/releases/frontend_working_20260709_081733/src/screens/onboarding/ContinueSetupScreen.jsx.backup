import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { FaClock, FaTrash, FaChevronRight, FaExclamationTriangle } from "react-icons/fa";
import { getDraft, clearDraft, formatTimeAgo, STEPS } from "../../services/onboardingDraftService";

// Map step keys to display names
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

// Map step keys to routes
const STEP_ROUTES = {
  'welcome': '/',
  'phone': '/login',
  'pin': '/create-pin',
  'user-details': '/user-details',
  'checkin': '/checkin-settings',
  'duress': '/settings/duress-pin',
  'dashboard': '/dashboard',
  'complete': '/dashboard',
};

function ContinueSetupScreen() {
  const navigate = useNavigate();
  const [draft, setDraft] = useState(null);
  const [showConfirm, setShowConfirm] = useState(false);

  useEffect(() => {
    const draftData = getDraft();
    if (!draftData) {
      // No draft, go to welcome
      navigate('/');
      return;
    }
    setDraft(draftData);
  }, [navigate]);

  const handleContinue = () => {
    if (!draft) return;
    const currentStep = draft.step || 'welcome';
    const route = STEP_ROUTES[currentStep] || '/';
    
    // If we're at the last step, go to dashboard
    if (currentStep === 'dashboard' || currentStep === 'complete') {
      navigate('/dashboard');
      return;
    }
    
    navigate(route);
  };

  const handleStartOver = () => {
    clearDraft();
    navigate('/');
  };

  if (!draft) {
    return (
      <div className="min-h-screen bg-[#F0F7F2] flex items-center justify-center">
        <div className="text-center">
          <div className="w-10 h-10 border-3 border-[#1A5632] border-t-transparent rounded-full animate-spin mx-auto mb-3" />
          <p className="text-[#1A5632] text-sm">Checking...</p>
        </div>
      </div>
    );
  }

  const stepName = STEP_NAMES[draft.step] || 'Setup';
  const timeAgo = formatTimeAgo(draft.updated_at);
  const phone = draft.phone || 'No phone saved';
  const userName = draft.profile?.name || '';

  return (
    <div className="min-h-screen bg-[#F0F7F2] flex items-center justify-center px-6">
      <div className="w-full max-w-md">
        
        {/* Logo */}
        <div className="text-center mb-8">
          <div className="w-16 h-16 mx-auto mb-3 rounded-full bg-gradient-to-br from-[#1A5632] to-[#0E3A22] shadow-lg flex items-center justify-center">
            <span className="material-symbols-outlined text-white text-3xl">shield</span>
          </div>
          <h1 className="text-2xl font-black text-[#1A5632] tracking-[0.2em]">KIN</h1>
          <p className="text-[#6C757D] text-xs mt-1">Personal Safety Network</p>
        </div>

        {/* Card */}
        <div className="bg-white rounded-2xl p-6 shadow-lg border border-[#E9ECEF]">
          <div className="text-center mb-6">
            <div className="w-12 h-12 mx-auto mb-3 rounded-full bg-[#E8F3EA] flex items-center justify-center">
              <FaClock className="text-[#1A5632] text-xl" />
            </div>
            <h2 className="text-xl font-bold text-[#1A1A1A]">Continue Setup</h2>
            <p className="text-sm text-[#6C757D] mt-1">
              You were setting up your KIN account
            </p>
          </div>

          {/* Progress Info */}
          <div className="bg-[#F8F9FA] rounded-xl p-4 mb-6">
            <div className="flex items-center justify-between mb-2">
              <span className="text-xs font-medium text-[#6C757D]">Last step</span>
              <span className="text-xs font-medium text-[#1A5632]">{stepName}</span>
            </div>
            <div className="flex items-center justify-between mb-2">
              <span className="text-xs font-medium text-[#6C757D]">Phone</span>
              <span className="text-xs font-medium text-[#1A1A1A]">{phone}</span>
            </div>
            {userName && (
              <div className="flex items-center justify-between">
                <span className="text-xs font-medium text-[#6C757D]">Name</span>
                <span className="text-xs font-medium text-[#1A1A1A]">{userName}</span>
              </div>
            )}
            <div className="mt-3 pt-3 border-t border-[#E9ECEF]">
              <span className="text-[10px] text-[#6C757D]">Last updated {timeAgo}</span>
            </div>
          </div>

          {/* Actions */}
          <div className="space-y-3">
            <button
              onClick={handleContinue}
              className="w-full h-12 rounded-xl bg-gradient-to-r from-[#1A5632] to-[#0E3A22] text-white font-semibold text-base flex items-center justify-center gap-2 shadow-lg hover:opacity-90 active:scale-95 transition-all"
            >
              Continue Setup
              <FaChevronRight className="text-sm" />
            </button>

            <button
              onClick={() => setShowConfirm(true)}
              className="w-full py-3 text-sm font-medium text-[#6C757D] hover:text-[#DC3545] transition-colors"
            >
              Start Over
            </button>
          </div>
        </div>

        {/* Version Info */}
        <p className="text-center text-[10px] text-[#6C757D] mt-6">
          KIN v1.0.0 • Protecting what matters
        </p>
      </div>

      {/* Confirm Start Over Modal */}
      {showConfirm && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-6">
          <div className="bg-white rounded-2xl p-6 w-full max-w-sm shadow-2xl">
            <div className="text-center mb-4">
              <div className="w-12 h-12 mx-auto mb-3 rounded-full bg-red-100 flex items-center justify-center">
                <FaExclamationTriangle className="text-red-500 text-xl" />
              </div>
              <h3 className="text-lg font-bold text-[#1A1A1A]">Start Over?</h3>
              <p className="text-sm text-[#6C757D] mt-1">
                This will clear all your setup progress.
              </p>
            </div>
            <div className="flex gap-3">
              <button
                onClick={() => setShowConfirm(false)}
                className="flex-1 h-11 rounded-xl bg-gray-100 text-[#6C757D] font-medium text-sm hover:bg-gray-200 transition"
              >
                Cancel
              </button>
              <button
                onClick={handleStartOver}
                className="flex-1 h-11 rounded-xl bg-red-500 text-white font-medium text-sm hover:bg-red-600 transition"
              >
                Yes, Start Over
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default ContinueSetupScreen;
