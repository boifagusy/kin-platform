function AssistanceButton({ onClick }) {
  return (
    <button
      onClick={onClick}
      style={{ background: "#fefce8", border: "1px solid #fde047", borderRadius: 16, padding: "14px 16px", display: "flex", alignItems: "center", gap: 12, cursor: "pointer", width: "100%" }}
    >
      <span style={{ fontSize: 24 }}>🟡</span>
      <span style={{ fontWeight: 500, color: "#854d0e" }}>Need Assistance</span>
    </button>
  );
}

export default AssistanceButton;
