import { useState } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import InviteModal from "../../components/trusted-contact/InviteModal";

function TrustedContactScreen() {
  const navigate = useNavigate();
  const location = useLocation();
  const phone = location.state?.phone;
  const fullName = location.state?.full_name || "User";

  const [contactName, setContactName] = useState("");
  const [contactPhone, setContactPhone] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [showInviteModal, setShowInviteModal] = useState(false);
  const [inviteSent, setInviteSent] = useState(false);
  const [contactAdded, setContactAdded] = useState(false);

  const canContinue = contactName.trim().length >= 3 && contactPhone.trim().length >= 10;

  // ✅ NEW INVITE MESSAGE
  const getInviteMessage = () => {
    const token = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
    const baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1';
    const verifyLink = `${baseUrl}/trusted-contact/verify/${token}`;
    const downloadLink = "https://kin.app";

    return `Hi ${contactName},

${fullName} has added you as a trusted contact on KIN, a personal safety app. This means you may receive an alert if they miss a scheduled check-in or get alerted in case of emergency.

Please confirm you're willing to take on this role:
👉 Confirm as Trusted Contact: ${verifyLink}

Don't have KIN yet? Download it here:
📲 Get KIN: ${downloadLink}

Thanks for helping keep ${fullName} safe.
— The KIN Team`;
  };

  const handleAddContact = () => {
    if (!canContinue) return;
    setContactAdded(true);
    setShowInviteModal(true);
  };

  const handleSmsShare = () => {
    const message = getInviteMessage();
    const phoneNumber = contactPhone.replace(/[^0-9]/g, '');
    window.open(`sms:${phoneNumber}?body=${encodeURIComponent(message)}`, '_blank');
    setInviteSent(true);
  };

  const handleWhatsappShare = () => {
    const message = getInviteMessage();
    const phoneNumber = contactPhone.replace(/[^0-9]/g, '');
    window.open(`https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`, '_blank');
    setInviteSent(true);
  };

  const handleContinueSetup = () => {
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
  };

  return (
    <div className="min-h-screen bg-[#F0F7F2] flex items-center justify-center px-6 py-8">
      <div className="w-full max-w-md">
        <div className="bg-white rounded-2xl p-6 shadow-lg">
          <h1 className="text-2xl font-bold text-[#1A5632] text-center">Add Trusted Contact</h1>
          <p className="text-[#6C757D] text-sm text-center mt-1">
            Who should we notify in case of emergency?
          </p>

          <div className="mt-6 space-y-4">
            <div>
              <label className="block text-sm font-medium text-[#1A5632] mb-1">Contact Name</label>
              <input
                type="text"
                value={contactName}
                onChange={(e) => setContactName(e.target.value)}
                placeholder="Full name"
                className="w-full h-12 px-4 rounded-xl border border-gray-200 focus:border-[#1A5632] focus:ring-2 focus:ring-[#1A5632]/20 outline-none transition-all"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-[#1A5632] mb-1">Phone Number</label>
              <input
                type="tel"
                value={contactPhone}
                onChange={(e) => setContactPhone(e.target.value)}
                placeholder="+234 801 234 5678"
                className="w-full h-12 px-4 rounded-xl border border-gray-200 focus:border-[#1A5632] focus:ring-2 focus:ring-[#1A5632]/20 outline-none transition-all"
              />
            </div>

            {error && <p className="text-red-500 text-sm text-center">{error}</p>}

            <button
              onClick={handleAddContact}
              disabled={!canContinue || loading}
              className={`w-full h-12 rounded-xl font-semibold text-white transition-all ${
                canContinue && !loading
                  ? 'bg-gradient-to-r from-[#1A5632] to-[#0E3A22] shadow-lg shadow-[#1A5632]/20 hover:opacity-90 active:scale-95'
                  : 'bg-gray-300 cursor-not-allowed'
              }`}
            >
              {loading ? 'Saving...' : 'Add Contact'}
            </button>
          </div>
        </div>
      </div>

      <InviteModal
        isOpen={showInviteModal}
        contactName={contactName}
        contactPhone={contactPhone}
        inviteMessage={getInviteMessage()}
        inviteSent={inviteSent}
        onSmsShare={handleSmsShare}
        onWhatsappShare={handleWhatsappShare}
        onContinueSetup={handleContinueSetup}
      />
    </div>
  );
}

export default TrustedContactScreen;
