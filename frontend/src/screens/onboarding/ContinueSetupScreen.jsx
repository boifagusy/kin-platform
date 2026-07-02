import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { FaClock, FaTrash, FaChevronRight, FaExclamationTriangle } from "react-icons/fa";
import { getDraft, clearDraft, formatTimeAgo, STEPS, syncDraft, saveToServer } from "../../services/onboardingDraftService";

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
  const [loading, setLoading] = useState(true);
  const [syncError, setSyncError] = useState(false);

  // Load draft from server + localStorage
  useEffect(() => {
    const loadDraft = async () => {
      setLoading(true);
      setSyncError(false);
      
      try {
        // Sync with server (loads from server, falls back to localStorage)
        const syncedDraft = await syncDraft();
        
        if (syncedDraft) {
          setDraft(syncedDraft);
        } else {
          // Fallback to localStorage only
          const localDraft = getDraft();
          if (localDraft) {
            setDraft(localDraft);
            // Try to sync local draft to server
            await saveToServer(localDraft);
          } else {
            // No draft, go to welcome
            navigate('/');
            return;
          }
        }
      } catch (error) {
        console.error('Error loading draft:', error);
        setSyncError(true);
        // Fallback to localStorage
        const localDraft = getDraft();
        if (localDraft) {
          setDraft(localDraft);
        } else {
          navigate('/');
          return;
        }
      } finally {
        setLoading(false);
      }
    };
    
    loadDraft();
  }, [navigate]);

  const handleContinue = () => {
    if (!draft) return;
    const currentStep = draft.step || 'welcome';
    const route = STEP_ROUTES[currentStep] || '/';

    // If we're at the last step, go to dashboard
    if (currentStep === 'complete' || currentStep === 'dashboard') {
      navigate('/dashboard');
      return;
    }

    navigate(route);
  };

  const handleClearDraft = async () => {
    clearDraft();
    setDraft(null);
    setShowConfirm(false);
    // Clear server draft too
    try {
      await saveToServer({ step: null, draft: null });
    } catch (error) {
      console.error('Error clearing server draft:', error);
    }
    navigate('/');
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-screen bg-gray-50">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto"></div>
          <p className="mt-4 text-gray-600">Loading your progress...</p>
        </div>
      </div>
    );
  }

  if (syncError) {
    return (
      <div className="flex items-center justify-center min-h-screen bg-gray-50 p-4">
        <div className="bg-white rounded-lg shadow-lg p-6 max-w-md w-full text-center">
          <FaExclamationTriangle className="text-yellow-500 text-5xl mx-auto mb-4" />
          <h2 className="text-xl font-semibold text-gray-800 mb-2">Connection Issue</h2>
          <p className="text-gray-600 mb-4">
            We're having trouble syncing your progress. Your draft is saved locally.
          </p>
          <button
            onClick={handleContinue}
            className="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors"
          >
            Continue Anyway
          </button>
        </div>
      </div>
    );
  }

  if (!draft) {
    return null;
  }

  const currentStep = draft.step || 'welcome';
  const stepName = STEP_NAMES[currentStep] || 'Unknown Step';
  const timeAgo = draft.updatedAt ? formatTimeAgo(draft.updatedAt) : 'recently';

  return (
    <div className="min-h-screen bg-gray-50 flex items-center justify-center p-4">
      <div className="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
        <div className="text-center mb-6">
          <div className="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
            <FaClock className="text-green-600 text-2xl" />
          </div>
          <h1 className="text-2xl font-bold text-gray-800">Continue Setup</h1>
          <p className="text-gray-500 mt-1">You left off at <strong>{stepName}</strong></p>
          <p className="text-sm text-gray-400 mt-1">Last saved {timeAgo}</p>
        </div>

        <div className="bg-gray-50 rounded-lg p-4 mb-6">
          <div className="flex justify-between items-center">
            <div>
              <p className="text-sm text-gray-500">Progress</p>
              <div className="flex items-center gap-2 mt-1">
                <div className="w-48 h-2 bg-gray-200 rounded-full overflow-hidden">
                  <div 
                    className="h-full bg-green-500 rounded-full transition-all duration-500"
                    style={{ 
                      width: `${Object.keys(STEPS).indexOf(currentStep) / Object.keys(STEPS).length * 100}%` 
                    }}
                  />
                </div>
                <span className="text-xs text-gray-500">
                  {Math.round(Object.keys(STEPS).indexOf(currentStep) / Object.keys(STEPS).length * 100)}%
                </span>
              </div>
            </div>
          </div>
        </div>

        <div className="space-y-3">
          <button
            onClick={handleContinue}
            className="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition-colors flex items-center justify-center gap-2"
          >
            Continue
            <FaChevronRight className="text-sm" />
          </button>
          
          <button
            onClick={() => setShowConfirm(true)}
            className="w-full text-gray-500 py-2 text-sm hover:text-red-600 transition-colors"
          >
            Start Over
          </button>
        </div>

        {showConfirm && (
          <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div className="bg-white rounded-xl max-w-sm w-full p-6">
              <h3 className="text-lg font-semibold text-gray-800 mb-2">Start Over?</h3>
              <p className="text-gray-600 text-sm mb-4">
                This will clear your progress and you'll start the onboarding process from the beginning.
              </p>
              <div className="flex gap-3">
                <button
                  onClick={() => setShowConfirm(false)}
                  className="flex-1 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                >
                  Cancel
                </button>
                <button
                  onClick={handleClearDraft}
                  className="flex-1 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center justify-center gap-2"
                >
                  <FaTrash className="text-sm" />
                  Start Over
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

export default ContinueSetupScreen;
