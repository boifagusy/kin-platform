import { FaPhoneAlt, FaLocationArrow, FaPaperPlane } from "react-icons/fa";

function AssistanceOptions({ onCall, onShareLocation, onSendAlert }) {
  return (
    <div style={{ background: "#fefce8", borderRadius: 20, padding: 16, marginBottom: 16, border: "1px solid #fde047" }}>
      <p style={{ fontSize: 14, fontWeight: "bold", color: "#854d0e", marginBottom: 12 }}>Need Assistance?</p>
      <div style={{ display: "flex", flexDirection: "column", gap: 10 }}>
        <button onClick={onCall} style={{ background: "white", border: "1px solid #e5e7eb", borderRadius: 12, padding: "12px 16px", display: "flex", alignItems: "center", gap: 12, cursor: "pointer", fontSize: 14, width: "100%" }}>
          <FaPhoneAlt style={{ color: "#1A5632" }} /> Call Trusted Contact
        </button>
        <button onClick={onShareLocation} style={{ background: "white", border: "1px solid #e5e7eb", borderRadius: 12, padding: "12px 16px", display: "flex", alignItems: "center", gap: 12, cursor: "pointer", fontSize: 14, width: "100%" }}>
          <FaLocationArrow style={{ color: "#1A5632" }} /> Share Live Location
        </button>
        <button onClick={onSendAlert} style={{ background: "white", border: "1px solid #e5e7eb", borderRadius: 12, padding: "12px 16px", display: "flex", alignItems: "center", gap: 12, cursor: "pointer", fontSize: 14, width: "100%" }}>
          <FaPaperPlane style={{ color: "#1A5632" }} /> Send Alert to Network
        </button>
      </div>
    </div>
  );
}

export default AssistanceOptions;
