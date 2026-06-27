import { useState } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import LoadingScreen from "../../components/ui/LoadingScreen";
import { saveTrustedContact } from "../../services/api";
import ProgressIndicator from "../../components/trusted-contact/ProgressIndicator";
import SafetyCircleCard from "../../components/trusted-contact/SafetyCircleCard";

function TrustedContactScreenV2() {
  const navigate = useNavigate();
  const location = useLocation();
  const phone = location.state?.phone;
  const fullName = location.state?.full_name || "User";

  const [contactName, setContactName] = useState("");
  const [contactPhone, setContactPhone] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  const canContinue = contactName.trim().length >= 3 && contactPhone.trim().length >= 10;

  const saveContactToBackend = async () => {
    try {
      await saveTrustedContact({ 
        phone, 
        contact_name: contactName, 
        contact_phone: contactPhone, 
        invite_sent: false 
      });
      console.log('✅ Trusted contact saved to backend');
    } catch (error) {
      console.error('❌ Failed to save trusted contact:', error);
    }
  };

  const handleContinue = async () => {
    if (!canContinue) return;
    
    setLoading(true);
    setError("");

    try {
      // Save contact to backend
      await saveContactToBackend();
      
      // Navigate to check-in settings WITHOUT sending invite
      navigate("/checkin-settings", { 
        state: { 
          phone, 
          full_name: fullName, 
          trusted_contact: { 
            name: contactName, 
            phone: contactPhone 
          } 
        } 
      });
    } catch (err) {
      setError("Failed to save contact. Please try again.");
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <LoadingScreen open={loading} message="contacts" />

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

        <div className="flex-1 flex flex-col items-center justify-center px-6 py-4">
          <div className="w-full max-w-md">
            
            {/* Logo */}
            <div className="text-center mb-3">
              <div className="w-12 h-12 mx-auto mb-2 rounded-full bg-gradient-to-br from-[#1A5632] to-[#0E3A22] shadow-lg flex items-center justify-center">
                <span className="material-symbols-outlined text-white text-xl">shield</span>
              </div>
              <h1 className="text-base font-black text-[#1A5632] tracking-[0.2em]">KIN</h1>
            </div>

            {/* Progress Indicator */}
            <ProgressIndicator currentStep={4} totalSteps={6} />

            {/* Heading */}
            <div className="text-center mt-4 mb-2">
              <h2 className="text-xl font-bold text-[#1A1A1A]">Add Trusted Contact</h2>
              <p className="text-[#6C757D] text-xs mt-1">
                Choose one person you trust. They may receive alerts if you miss a safety check-in or activate SOS.
              </p>
              <p className="text-[#D4A017] text-[10px] mt-2 font-medium">
                ⚡ You'll send the invite after setting up check-ins
              </p>
            </div>

            {/* Name Input */}
            <div className="mt-4">
              <label className="block text-xs font-medium text-[#6C757D] mb-1">
                Trusted Contact Name
              </label>
              <div className="bg-white rounded-xl px-4 py-3 shadow-sm border border-[#E9ECEF] flex items-center gap-3">
                <span className="material-symbols-outlined text-[#1A5632] text-xl">person</span>
                <input
                  type="text"
                  value={contactName}
                  onChange={(e) => setContactName(e.target.value)}
                  placeholder="Sarah Johnson"
                  className="flex-1 border-none outline-none bg-transparent text-base placeholder:text-[#6C757D]"
                />
              </div>
            </div>

            {/* Phone Input */}
            <div className="mt-3">
              <label className="block text-xs font-medium text-[#6C757D] mb-1">
                Phone Number
              </label>
              <div className="bg-white rounded-xl px-4 py-3 shadow-sm border border-[#E9ECEF] flex items-center gap-3">
                <span className="material-symbols-outlined text-[#1A5632] text-xl">phone</span>
                <input
                  type="tel"
                  value={contactPhone}
                  onChange={(e) => setContactPhone(e.target.value)}
                  placeholder="08012345678"
                  className="flex-1 border-none outline-none bg-transparent text-base placeholder:text-[#6C757D]"
                />
              </div>
            </div>

            {error && (
              <p className="text-red-500 text-xs text-center mt-2">{error}</p>
            )}

            {/* Safety Circle Card */}
            <div className="mt-4">
              <SafetyCircleCard />
            </div>

            {/* Continue Button - Navigates to Check-In Settings */}
            <button
              disabled={!canContinue || loading}
              onClick={handleContinue}
              className={`w-full h-12 rounded-xl font-semibold text-base flex items-center justify-center gap-2 mt-4 ${
                !canContinue || loading
                  ? "bg-[#B7D4BF] text-white cursor-not-allowed"
                  : "bg-gradient-to-r from-[#1A5632] to-[#0E3A22] text-white shadow-lg active:scale-95"
              }`}
            >
              {loading ? "Saving..." : "Continue to Check-In Settings"}
              {!loading && (
                <span className="material-symbols-outlined text-base">chevron_right</span>
              )}
            </button>
          </div>
        </div>
      </div>
    </>
  );
}

export default TrustedContactScreenV2;
