import { useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { FaUserShield } from "react-icons/fa";
import { hasDraft } from "../../services/onboardingStorage";

function WelcomeScreen() {
  const navigate = useNavigate();

  useEffect(() => {
    if (hasDraft()) {
      navigate("/continue-setup");
    }
  }, [navigate]);

  return (
    <div
      style={{
        minHeight: "100vh",
        background: "#F0F7F2",
        padding: "24px",
        display: "flex",
        flexDirection: "column",
        justifyContent: "center",
      }}
    >
      <div
        style={{
          textAlign: "center",
          marginBottom: "60px",
        }}
      >
        <FaUserShield size={120} color="#1A5632" />

        <h1
          style={{
            marginTop: "24px",
            fontSize: "38px",
            fontWeight: "700",
            color: "#111827",
          }}
        >
          Welcome to Safety
        </h1>

        <p
          style={{
            marginTop: "18px",
            color: "#6B7280",
            fontSize: "18px",
            lineHeight: "1.8",
            maxWidth: "380px",
            marginLeft: "auto",
            marginRight: "auto",
          }}
        >
          Stay connected with trusted family and friends. Share your location when
          it matters most and receive help quickly during emergencies.
        </p>
      </div>

      <button
        onClick={() => navigate("/login")}
        style={{
          width: "100%",
          height: "56px",
          border: "none",
          borderRadius: "16px",
          background: "#1A5632",
          color: "#FFFFFF",
          fontSize: "18px",
          fontWeight: "600",
        }}
      >
        Get Started
      </button>

      <button
        onClick={() => navigate("/login")}
        style={{
          width: "100%",
          height: "50px",
          marginTop: "12px",
          border: "none",
          background: "transparent",
          color: "#1A5632",
          fontSize: "16px",
          fontWeight: "600",
        }}
      >
        Log In
      </button>
    </div>
  );
}

export default WelcomeScreen;
