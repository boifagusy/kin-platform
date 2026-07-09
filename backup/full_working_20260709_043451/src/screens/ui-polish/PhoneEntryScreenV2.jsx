import { updatePhone, updateStep, STEPS } from "../../services/onboardingDraftService";
import { useState } from "react";
import { useNavigate } from "react-router-dom";
import LoadingScreen from "../../components/ui/LoadingScreen";
import AccountFoundModal from "../../components/ui-polish/AccountFoundModal";
import AccountNotFoundModal from "../../components/ui-polish/AccountNotFoundModal";

// Environment variable for API URL
const API_BASE = import.meta.env.VITE_API_URL;

function PhoneEntryScreenV2() {
  const [phone, setPhone] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [maskedPhone, setMaskedPhone] = useState("");
  const [showAccountFound, setShowAccountFound] = useState(false);
  const [showAccountNotFound, setShowAccountNotFound] = useState(false);
  const navigate = useNavigate();

  const isValid = phone.length === 10;

  // Guard double submit
  async function handleContinue() {
    if (loading) return;
    
    try {
      setLoading(true);
    // Save draft
    updatePhone(`+234${phone}`);
    updateStep(STEPS.PIN);
      setError("");

      const response = await fetch(
        `${API_BASE}/auth/confirm-phone`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            phone: `+234${phone}`,
          }),
        }
      );

      const data = await response.json();
      setMaskedPhone(data.masked_phone);

      if (data.exists) {
        setShowAccountFound(true);
      } else {
        setShowAccountNotFound(true);
      }
    } catch (err) {
      console.error(err);
      setError("Unable to connect to Kin server.");
    } finally {
      setLoading(false);
    }
  }

  // Enter key support
  const handleKeyDown = (e) => {
    if (e.key === "Enter" && isValid && !loading) {
      handleContinue();
    }
  };

  const handleEnterPin = () => {
    setShowAccountFound(false);
    localStorage.setItem("kin_phone", `+234${phone}`);
    navigate("/login-pin", {
      state: { phone: `+234${phone}` },
    });
  };

  const handleChangeNumber = () => {
    setShowAccountFound(false);
    setShowAccountNotFound(false);
  };

  const handleContinueNewUser = () => {
    setShowAccountNotFound(false);
    localStorage.setItem("kin_phone", `+234${phone}`);
    navigate("/create-pin", {
      state: { phone: `+234${phone}` },
    });
  };

  return (
    <>
      <LoadingScreen open={loading} message="auth" />

      {/* Fixed to screen - no scrolling */}
      <div className="fixed inset-0 bg-gradient-to-br from-[#F0F7F2] to-[#E8F3EA] flex items-center justify-center">
        <div className="absolute top-[-100px] left-[-100px] w-80 h-80 bg-[#1A5632]/10 rounded-full blur-3xl" />
        <div className="absolute bottom-[-100px] right-[-100px] w-80 h-80 bg-[#D4A017]/10 rounded-full blur-3xl" />

        {/* Content container - fits exactly on screen */}
        <div className="w-full max-w-md mx-auto px-6 py-6">
          <div className="flex flex-col h-full">
            
            {/* Logo Section */}
            <div className="text-center">
              <div className="w-20 h-20 mx-auto mb-3 rounded-full bg-gradient-to-br from-[#1A5632] to-[#0E3A22] shadow-xl shadow-[#1A5632]/15 flex items-center justify-center">
                <span className="material-symbols-outlined text-white text-3xl" style={{ fontVariationSettings: "'FILL' 1" }}>
                  shield
                </span>
              </div>
              <h1 className="text-2xl font-black text-[#1A5632] tracking-[0.2em]">KIN</h1>
              <p className="text-[#6C757D] text-xs mt-1">Personal Safety Network</p>
            </div>

            {/* Main Content - Flex grow to fill space */}
            <div className="flex-1 flex flex-col justify-center">
              {/* Heading */}
              <div className="text-center">
                <h2 className="text-2xl font-bold text-[#1A1A1A]">
                  Enter your phone number
                </h2>
                <p className="text-[#6C757D] text-sm mt-2">
                  Let's securely verify your phone number.
                </p>
              </div>

              {/* Premium Phone Number Card */}
              <div className="mt-6 bg-white rounded-2xl p-2 shadow-sm border border-[#E9ECEF]">
                <div className="h-14 flex items-center">
                  <div className="px-4 flex items-center gap-2 border-r border-[#E9ECEF]">
                    <span className="text-lg">🇳🇬</span>
                    <span className="font-semibold text-[#1A5632]">+234</span>
                  </div>
                  <input
                    type="tel"
                    value={phone}
                    maxLength={10}
                    placeholder="8012345678"
                    onChange={(e) => setPhone(e.target.value.replace(/\D/g, ""))}
                    onKeyDown={handleKeyDown}
                    className="flex-1 px-4 outline-none bg-transparent text-base"
                    autoFocus
                  />
                </div>
              </div>

              {/* Live Validation Feedback */}
              {phone.length === 10 && !error && (
                <div className="flex items-center justify-center gap-1 mt-2">
                  <span className="material-symbols-outlined text-green-600 text-sm">
                    check_circle
                  </span>
                  <span className="text-xs text-green-600 font-medium">
                    Valid phone number
                  </span>
                </div>
              )}
              
              {error && (
                <p className="text-[#DC3545] text-xs mt-2 text-center">{error}</p>
              )}

              {/* Contextual Reassurance */}
              <p className="text-xs text-[#6C757D] text-center mt-3">
                We never share your phone number with other users.
              </p>

              {/* Continue Button */}
              <div className="mt-5">
                <button
                  disabled={!isValid || loading}
                  onClick={handleContinue}
                  className={`w-full h-14 rounded-xl font-semibold text-lg transition-all duration-200 flex items-center justify-center gap-2 ${
                    !isValid || loading
                      ? "bg-[#B7D4BF] text-white cursor-not-allowed"
                      : "bg-gradient-to-r from-[#1A5632] to-[#0E3A22] text-white shadow-lg shadow-[#1A5632]/20 hover:scale-[1.01] active:scale-[0.98]"
                  }`}
                >
                  {loading ? "Verifying phone number..." : "Continue"}
                  {!loading && (
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                  )}
                </button>
              </div>

              {/* Trust Section */}
              <div className="mt-5 text-center">
                <div className="flex items-center justify-center gap-2">
                  <span className="material-symbols-outlined text-sm text-[#1A5632]">
                    encryption
                  </span>
                  <span className="text-xs text-[#6C757D] font-medium">
                    Protected with end-to-end encryption
                  </span>
                </div>
              </div>
            </div>

            {/* Footer - Fixed at bottom */}
            <div className="pt-4 pb-2">
              <p className="text-[#6C757D] text-xs text-center">
                Your location is never shared without your permission.
              </p>
            </div>
          </div>
        </div>
      </div>

      {/* Modals */}
      <AccountFoundModal
        open={showAccountFound}
        phoneNumber={`+234${phone}`}
        onEnterPin={handleEnterPin}
        onChangeNumber={handleChangeNumber}
      />

      <AccountNotFoundModal
        open={showAccountNotFound}
        phoneNumber={`+234${phone}`}
        onContinue={handleContinueNewUser}
        onChangeNumber={handleChangeNumber}
      />
    </>
  );
}

export default PhoneEntryScreenV2;
