import { useState } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import { FaArrowLeft, FaUser, FaPhone } from "react-icons/fa";
import { saveTrustedContact } from "../../services/api";
import ProgressIndicator from "../../components/trusted-contact/ProgressIndicator";
import SafetyCircleCard from "../../components/trusted-contact/SafetyCircleCard";
import InviteModal from "../../components/trusted-contact/InviteModal";

function TrustedContactScreen() {
  const navigate = useNavigate();
  const location = useLocation();
  const phone = location.state?.phone;
  const fullName = location.state?.full_name || "User";

  const [contactName, setContactName] = useState("");
  const [contactPhone, setContactPhone] = useState("");
  const [showInviteModal, setShowInviteModal] = useState(false);
  const [inviteSent, setInviteSent] = useState(false);

  const canSendInvite = contactName.trim().length >= 3 && contactPhone.trim().length >= 10;

  const inviteMessage = `Hi ${contactName || "there"},

${fullName} has added you as a trusted contact in KIN.

You may receive alerts if:
• A check-in is missed
• SOS is activated
• An emergency is detected

Download Kin:
https://kin.app`;

  const saveContactToBackend = async () => {
    try {
      await saveTrustedContact({ phone, contact_name: contactName, contact_phone: contactPhone, invite_sent: inviteSent });
      console.log('✅ Trusted contact saved to backend');
    } catch (error) {
      console.error('❌ Failed to save trusted contact:', error);
    }
  };

  const handleSendInvite = () => setShowInviteModal(true);

  const handleSmsShare = () => {
    window.open(`sms:${contactPhone}?body=${encodeURIComponent(inviteMessage)}`);
    setInviteSent(true);
    saveContactToBackend();
  };

  const handleWhatsappShare = () => {
    const cleaned = contactPhone.replace(/\D/g, "");
    window.open(`https://wa.me/${cleaned}?text=${encodeURIComponent(inviteMessage)}`);
    setInviteSent(true);
    saveContactToBackend();
  };

  const handleContinueSetup = () => {
    console.log("Trusted contact saved:", { contactName, contactPhone });
    navigate("/checkin-settings", { state: { phone, full_name: fullName, trusted_contact: { name: contactName, phone: contactPhone } } });
  };

  return (
    <div style={{ minHeight: "100vh", background: "linear-gradient(180deg,#F8FBF9 0%,#EDF5F0 100%)", display: "flex", justifyContent: "center", padding: "24px", boxSizing: "border-box" }}>
      <div style={{ width: "100%", maxWidth: "430px", paddingTop: "24px" }}>
        <button onClick={() => navigate(-1)} style={{ border: "none", background: "transparent", marginBottom: "24px", cursor: "pointer" }}>
          <FaArrowLeft size={22} color="#1A5632" />
        </button>

        <ProgressIndicator currentStep={4} totalSteps={6} />

        <h1 style={{ textAlign: "center", fontSize: "34px", fontWeight: "800", color: "#111827", marginBottom: "12px" }}>Add Trusted Contact</h1>
        <p style={{ textAlign: "center", color: "#6B7280", lineHeight: "28px", marginBottom: "40px" }}>
          Choose one person you trust. They may receive alerts if you miss a safety check-in or activate SOS.
        </p>

        <div style={{ marginBottom: "20px" }}>
          <div style={{ marginBottom: "8px", fontWeight: "600", color: "#6B7280" }}>Trusted Contact Name</div>
          <div style={{ background: "#FFFFFF", borderRadius: "20px", border: "2px solid #E5E7EB", padding: "18px", display: "flex", alignItems: "center" }}>
            <FaUser color="#1A5632" />
            <input type="text" value={contactName} onChange={(e) => setContactName(e.target.value)} placeholder="Sarah Johnson" style={{ flex: 1, border: "none", outline: "none", marginLeft: "12px", fontSize: "18px" }} />
          </div>
        </div>

        <div style={{ marginBottom: "36px" }}>
          <div style={{ marginBottom: "8px", fontWeight: "600", color: "#6B7280" }}>Phone Number</div>
          <div style={{ background: "#FFFFFF", borderRadius: "20px", border: "2px solid #E5E7EB", padding: "18px", display: "flex", alignItems: "center" }}>
            <FaPhone color="#1A5632" />
            <input type="tel" value={contactPhone} onChange={(e) => setContactPhone(e.target.value)} placeholder="08012345678" style={{ flex: 1, border: "none", outline: "none", marginLeft: "12px", fontSize: "18px" }} />
          </div>
        </div>

        <SafetyCircleCard />

        <button disabled={!canSendInvite} onClick={handleSendInvite} style={{ width: "100%", height: "68px", border: "none", borderRadius: "22px", background: canSendInvite ? "linear-gradient(90deg,#1A5632,#3A7D44)" : "#B7D4BF", color: "#FFFFFF", fontSize: "18px", fontWeight: "700", cursor: canSendInvite ? "pointer" : "not-allowed" }}>
          Preview Invite
        </button>

        <InviteModal isOpen={showInviteModal} contactName={contactName} contactPhone={contactPhone} inviteMessage={inviteMessage} inviteSent={inviteSent} onSmsShare={handleSmsShare} onWhatsappShare={handleWhatsappShare} onContinueSetup={handleContinueSetup} />
      </div>
    </div>
  );
}

export default TrustedContactScreen;
