import React from 'react';
import { FaSms, FaWhatsapp, FaCheckCircle } from "react-icons/fa";

const InviteModal = ({ isOpen, contactName, contactPhone, inviteMessage, inviteSent, onSmsShare, onWhatsappShare, onContinueSetup }) => {
  if (!isOpen) return null;

  return (
    <div style={{ position: "fixed", inset: 0, background: "rgba(0,0,0,0.75)", display: "flex", justifyContent: "center", alignItems: "center", padding: "20px", zIndex: 1000 }}>
      <div style={{ width: "100%", maxWidth: "420px", background: "#FFFFFF", borderRadius: "24px", padding: "24px" }}>
        <h2 style={{ color: "#111827", marginBottom: "16px", fontSize: "24px", fontWeight: "700" }}>Invite Preview</h2>
        <div style={{ background: "#F8FBF9", borderRadius: "16px", padding: "18px", lineHeight: "26px", color: "#374151", whiteSpace: "pre-line", marginBottom: "20px" }}>
          {inviteMessage}
        </div>
        {!inviteSent ? (
          <>
            <button onClick={onSmsShare} style={{ width: "100%", height: "56px", marginBottom: "12px", border: "none", borderRadius: "16px", background: "#D4A017", color: "#FFFFFF", fontWeight: "700", cursor: "pointer", display: "flex", alignItems: "center", justifyContent: "center", gap: "8px" }}>
              <FaSms /> Send via SMS
            </button>
            <button onClick={onWhatsappShare} style={{ width: "100%", height: "56px", marginBottom: "12px", border: "none", borderRadius: "16px", background: "#25D366", color: "#FFFFFF", fontWeight: "700", cursor: "pointer", display: "flex", alignItems: "center", justifyContent: "center", gap: "8px" }}>
              <FaWhatsapp /> Send via WhatsApp
            </button>
          </>
        ) : (
          <div style={{ marginTop: "16px", textAlign: "center" }}>
            <div style={{ display: "flex", alignItems: "center", justifyContent: "center", gap: "8px", color: "#16A34A", fontWeight: "700", marginBottom: "16px" }}>
              <FaCheckCircle /> <span>Invite Sent!</span>
            </div>
            <div style={{ color: "#374151", fontSize: "14px", marginBottom: "20px" }}>{contactName} has been added to your Safety Circle.</div>
            <button onClick={onContinueSetup} style={{ width: "100%", height: "56px", border: "none", borderRadius: "16px", background: "linear-gradient(90deg,#1A5632,#3A7D44)", color: "#FFFFFF", fontWeight: "700", cursor: "pointer" }}>
              Continue Setup
            </button>
          </div>
        )}
      </div>
    </div>
  );
};

export default InviteModal;
