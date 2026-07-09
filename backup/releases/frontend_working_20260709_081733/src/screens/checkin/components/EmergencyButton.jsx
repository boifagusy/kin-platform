function EmergencyButton({ onClick }) {
  return (
    <button
      onClick={onClick}
      style={{ background: "#fef2f2", border: "1px solid #fecaca", borderRadius: 16, padding: "14px 16px", display: "flex", alignItems: "center", gap: 12, cursor: "pointer", width: "100%" }}
    >
      <span style={{ fontSize: 24 }}>🔴</span>
      <span style={{ fontWeight: 500, color: "#991b1b" }}>Emergency</span>
    </button>
  );
}

export default EmergencyButton;
