import { FaShieldAlt } from "react-icons/fa";

function StatusCard({ score, label }) {
  return (
    <div style={{ background: "linear-gradient(135deg, #1A5632 0%, #2F6A44 100%)", borderRadius: 24, padding: 20, color: "white", boxShadow: "0 4px 6px rgba(0,0,0,0.1)" }}>
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
        <div>
          <p style={{ fontSize: 12, opacity: 0.8, margin: 0 }}>Safety Score</p>
          <p style={{ fontSize: 36, fontWeight: "bold", margin: "4px 0 0 0" }}>{score}%</p>
          <p style={{ fontSize: 12, opacity: 0.9, margin: "4px 0 0 0" }}>{label}</p>
        </div>
        <div style={{ width: 56, height: 56, borderRadius: 28, background: "rgba(255,255,255,0.2)", display: "flex", alignItems: "center", justifyContent: "center" }}>
          <FaShieldAlt style={{ fontSize: 28, color: "white" }} />
        </div>
      </div>
    </div>
  );
}

export default StatusCard;
