function SafetyCheckCard({
  checkInState,
  nextCheckin,
  onSafe,
  onAssistance,
  onEmergency,
  onUpdateStatus,
  hasTrustedContact = true,
  onAddContact
}) {

  if (checkInState === "safe") {
    return (
      <div style={{
        background: "white",
        borderRadius: 24,
        padding: 20,
        marginBottom: 16,
        textAlign: "center",
        boxShadow: "0 1px 3px rgba(0,0,0,0.1)"
      }}>
        <div style={{ fontSize: 48 }}>✅</div>

        <h3 style={{
          color: "#1A5632",
          fontSize: 22,
          fontWeight: "bold",
          marginTop: 12
        }}>
          Check-in Recorded
        </h3>

        <p style={{
          color: "#6b7280",
          marginTop: 8
        }}>
          Next Check-In: {nextCheckin}
        </p>

        <div style={{
          marginTop: 12,
          display: "inline-block",
          padding: "8px 12px",
          borderRadius: 20,
          background: "#DCFCE7",
          color: "#166534",
          fontWeight: "bold",
          fontSize: 13
        }}>
          +5 Safety Score
        </div>

        <button
          onClick={onUpdateStatus}
          style={{
            display: "block",
            margin: "16px auto 0",
            border: "none",
            background: "transparent",
            color: "#1A5632",
            cursor: "pointer"
          }}
        >
          Update Status
        </button>
      </div>
    );
  }

  return (
    <div style={{
      background: "white",
      borderRadius: 24,
      padding: 20,
      marginBottom: 16,
      boxShadow: "0 1px 3px rgba(0,0,0,0.1)"
    }}>

      <p style={{
        color: "#6b7280",
        fontSize: 12,
        fontWeight: 600,
        letterSpacing: 1
      }}>
        DAILY SAFETY CHECK
      </p>

      <h2 style={{
        color: "#1A5632",
        fontSize: 24,
        fontWeight: "bold",
        marginTop: 8
      }}>
        How are you right now?
      </h2>

      <div style={{
        display: "flex",
        flexDirection: "column",
        gap: 12,
        marginTop: 20
      }}>

        <button
          onClick={onSafe}
          disabled={!hasTrustedContact}
          style={{
            background: hasTrustedContact ? "#DCFCE7" : "#F3F4F6",
            color: hasTrustedContact ? "#166534" : "#9CA3AF",
            border: "none",
            borderRadius: 16,
            padding: "16px",
            fontWeight: "bold",
            fontSize: 16,
            cursor: hasTrustedContact ? "pointer" : "not-allowed"
          }}
        >
          🟢 I'm Safe
        </button>

        <button
          onClick={onAssistance}
          style={{
            background: "#FEF3C7",
            color: "#92400E",
            border: "none",
            borderRadius: 16,
            padding: "16px",
            fontWeight: "bold",
            fontSize: 16,
            cursor: "pointer"
          }}
        >
          🟡 Need Assistance
        </button>

        <button
          onClick={onEmergency}
          disabled={!hasTrustedContact}
          style={{
            background: hasTrustedContact ? "#FEE2E2" : "#F3F4F6",
            color: hasTrustedContact ? "#B91C1C" : "#9CA3AF",
            border: "none",
            borderRadius: 16,
            padding: "16px",
            fontWeight: "bold",
            fontSize: 16,
            cursor: hasTrustedContact ? "pointer" : "not-allowed"
          }}
        >
          🔴 Emergency
        </button>

      </div>

      {!hasTrustedContact && (
        <div style={{
          marginTop: 16,
          padding: "12px 16px",
          background: "#FEF3C7",
          borderRadius: 12,
          textAlign: "center"
        }}>
          <p style={{ color: "#92400E", fontSize: 13, marginBottom: 8 }}>
            Add a trusted contact first to enable check-ins and SOS.
          </p>
          <button
            onClick={onAddContact}
            style={{
              border: "none",
              background: "transparent",
              color: "#1A5632",
              fontWeight: "bold",
              fontSize: 13,
              cursor: "pointer",
              textDecoration: "underline"
            }}
          >
            Add Trusted Contact →
          </button>
        </div>
      )}
    </div>
  );
}

export default SafetyCheckCard;
