import { FaExclamationTriangle } from "react-icons/fa";

function EmergencyModal({ onConfirm, onCancel }) {
  return (
    <div style={{ position: "fixed", inset: 0, background: "rgba(0,0,0,0.5)", display: "flex", alignItems: "center", justifyContent: "center", zIndex: 50, padding: 20 }}>
      <div style={{ background: "white", borderRadius: 24, padding: 24, maxWidth: 320, width: "100%" }}>
        <div style={{ textAlign: "center", marginBottom: 20 }}>
          <div style={{ width: 56, height: 56, background: "#fef2f2", borderRadius: 28, display: "flex", alignItems: "center", justifyContent: "center", margin: "0 auto 12px" }}>
            <FaExclamationTriangle style={{ fontSize: 28, color: "#dc2626" }} />
          </div>
          <h3 style={{ fontSize: 20, fontWeight: "bold", marginBottom: 8 }}>Send SOS Alert?</h3>
          <p style={{ fontSize: 14, color: "#6b7280" }}>Your trusted contacts will be notified immediately.</p>
        </div>
        <div style={{ display: "flex", gap: 12 }}>
          <button onClick={onCancel} style={{ flex: 1, padding: "12px", borderRadius: 16, border: "1px solid #e5e7eb", background: "white", cursor: "pointer" }}>Cancel</button>
          <button onClick={onConfirm} style={{ flex: 1, padding: "12px", borderRadius: 16, background: "#dc2626", color: "white", border: "none", cursor: "pointer" }}>Send SOS</button>
        </div>
      </div>
    </div>
  );
}

export default EmergencyModal;
