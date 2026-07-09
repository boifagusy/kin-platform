function SafeButton({ onClick }) {
  return (
    <button
      onClick={onClick}
      style={{ background: "#f0fdf4", border: "1px solid #bbf7d0", borderRadius: 16, padding: "14px 16px", display: "flex", alignItems: "center", gap: 12, cursor: "pointer", width: "100%" }}
    >
      <span style={{ fontSize: 24 }}>🟢</span>
      <span style={{ fontWeight: 500, color: "#166534" }}>I'm Safe</span>
    </button>
  );
}

export default SafeButton;
