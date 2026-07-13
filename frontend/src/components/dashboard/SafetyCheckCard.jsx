function SafetyCheckCard({
  state,
  nextCheckin,
  hasTrustedContact,
  hasActiveSOS,
  onSafe,
  onAssistance,
  onEmergency,
  onAddContact
}) {
  const safeDisabled = !hasTrustedContact || state === 'checking' || state === 'done';
  const emergencyDisabled = !hasTrustedContact || state === 'checking' || hasActiveSOS;

  const safeLabel = () => {
    if (state === 'checking') return '⏳ Sending...';
    return "🟢 I'm Safe";
  };

  return (
    <div style={{ background: "white", borderRadius: 24, padding: 20, marginBottom: 16, boxShadow: "0 1px 3px rgba(0,0,0,0.1)" }}>
      <p style={{ color: "#6b7280", fontSize: 12, fontWeight: 600, letterSpacing: 1 }}>DAILY SAFETY CHECK</p>
      <h2 style={{ color: "#1A5632", fontSize: 24, fontWeight: "bold", marginTop: 8 }}>How are you right now?</h2>

      <div style={{ display: "flex", flexDirection: "column", gap: 12, marginTop: 20 }}>
        <button onClick={onSafe} disabled={safeDisabled}
          style={{ background: !safeDisabled ? "#DCFCE7" : "#F3F4F6", color: !safeDisabled ? "#166534" : "#9CA3AF", border: "none", borderRadius: 16, padding: "16px", fontWeight: "bold", fontSize: 16, cursor: !safeDisabled ? "pointer" : "not-allowed" }}>
          {safeLabel()}
        </button>

        {state === 'done' && (
          <p style={{ color: "#166534", fontSize: 12, margin: "-8px 0 0 0", textAlign: "center" }}>✅ Check-in recorded. Come back at {nextCheckin}</p>
        )}
        {state === 'offline' && (
          <p style={{ color: "#D4A017", fontSize: 12, margin: "-8px 0 0 0", textAlign: "center" }}>📴 Saved offline. Will send when online.</p>
        )}
        {state === 'error' && (
          <p style={{ color: "#DC2626", fontSize: 12, margin: "-8px 0 0 0", textAlign: "center" }}>❌ Failed to send. Tap to retry.</p>
        )}

        <button onClick={onAssistance} style={{ background: "#FEF3C7", color: "#92400E", border: "none", borderRadius: 16, padding: "16px", fontWeight: "bold", fontSize: 16, cursor: "pointer" }}>
          🟡 Need Assistance
        </button>

        <button onClick={onEmergency} disabled={emergencyDisabled}
          style={{ background: !emergencyDisabled ? "#FEE2E2" : "#F3F4F6", color: !emergencyDisabled ? "#B91C1C" : "#9CA3AF", border: "none", borderRadius: 16, padding: "16px", fontWeight: "bold", fontSize: 16, cursor: !emergencyDisabled ? "pointer" : "not-allowed" }}>
          {hasActiveSOS ? '🔴 SOS Active' : '🔴 Emergency'}
        </button>

        {hasActiveSOS && (
          <div style={{ background: "#FEE2E2", borderRadius: 12, padding: "12px", border: "1px solid #FCA5A5", marginTop: 4 }}>
            <p style={{ color: "#B91C1C", fontWeight: "bold", fontSize: 13, margin: 0 }}>🚨 Active Emergency</p>
            <p style={{ color: "#DC2626", fontSize: 11, marginTop: 2 }}>Responders have been notified.</p>
          </div>
        )}
      </div>

      {!hasTrustedContact && (
        <div style={{ marginTop: 16, padding: "12px 16px", background: "#FEF3C7", borderRadius: 12, textAlign: "center" }}>
          <p style={{ color: "#92400E", fontSize: 13, marginBottom: 8 }}>Add a trusted contact first to enable check-ins and SOS.</p>
          <button onClick={onAddContact} style={{ border: "none", background: "transparent", color: "#1A5632", fontWeight: "bold", fontSize: 13, cursor: "pointer", textDecoration: "underline" }}>Add Trusted Contact →</button>
        </div>
      )}
    </div>
  );
}
export default SafetyCheckCard;
