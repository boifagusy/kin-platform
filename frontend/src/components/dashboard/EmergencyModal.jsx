import { useEffect } from "react";

function EmergencyModal({ onConfirm, onCancel }) {
  useEffect(() => {
    document.body.style.overflow = "hidden";
    return () => {
      document.body.style.overflow = "auto";
    };
  }, []);

  return (
    <div
      style={{
        position: "fixed",
        top: 0,
        left: 0,
        right: 0,
        bottom: 0,
        background: "rgba(0,0,0,0.7)",
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        zIndex: 99999,
        padding: "20px",
      }}
      onClick={onCancel}
    >
      <div
        style={{
          background: "white",
          borderRadius: "28px",
          padding: "32px 24px",
          width: "100%",
          maxWidth: "380px",
          boxShadow: "0 20px 60px rgba(0,0,0,0.4)",
        }}
        onClick={(e) => e.stopPropagation()}
      >
        <div
          style={{
            width: "72px",
            height: "72px",
            borderRadius: "50%",
            background: "#DC3545",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            margin: "0 auto 16px",
          }}
        >
          <span style={{ fontSize: "36px", color: "white" }}>🚨</span>
        </div>

        <h2 style={{
          color: "#1a1a1a",
          fontSize: "22px",
          fontWeight: "700",
          textAlign: "center",
          marginBottom: "8px",
        }}>
          Emergency Alert
        </h2>

        <p style={{
          color: "#6b7280",
          fontSize: "15px",
          textAlign: "center",
          lineHeight: "1.5",
        }}>
          Send SOS alert to your trusted contacts?
        </p>

        <div style={{
          display: "flex",
          gap: "12px",
          marginTop: "24px",
        }}>
          <button
            onClick={onCancel}
            style={{
              flex: 1,
              padding: "16px",
              borderRadius: "14px",
              border: "2px solid #e5e7eb",
              background: "white",
              color: "#6b7280",
              fontSize: "16px",
              fontWeight: "600",
            }}
          >
            Cancel
          </button>

          <button
            onClick={onConfirm}
            style={{
              flex: 1,
              padding: "16px",
              borderRadius: "14px",
              border: "none",
              background: "#DC3545",
              color: "white",
              fontSize: "16px",
              fontWeight: "700",
              boxShadow: "0 4px 16px rgba(220,53,69,0.3)",
            }}
          >
            Send SOS
          </button>
        </div>
      </div>
    </div>
  );
}

export default EmergencyModal;
