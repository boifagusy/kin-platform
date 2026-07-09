import React from "react";
import { FaShieldAlt } from "react-icons/fa";

function NumberRegisteredPopup({
open,
phone,
onContinue,
onClose,
}) {
if (!open) return null;

return (
<div
style={{
position: "fixed",
inset: 0,
background:
"rgba(0,0,0,.45)",
display: "flex",
alignItems: "center",
justifyContent: "center",
padding: "24px",
zIndex: 999,
}}
>
<div
style={{
width: "100%",
maxWidth: "380px",
background: "#FFFFFF",
borderRadius: "28px",
padding: "32px",
textAlign: "center",
boxShadow:
"0 20px 40px rgba(26,86,50,.15)",
}}
>
<div
style={{
width: "80px",
height: "80px",
borderRadius: "50%",
background: "#EDF5F0",
display: "flex",
alignItems: "center",
justifyContent: "center",
margin: "0 auto 20px",
}}
>
<FaShieldAlt
size={34}
color="#1A5632"
/>
</div>

    <h2
      style={{
        margin: 0,
        color: "#111827",
        fontSize: "28px",
        fontWeight: "700",
      }}
    >
      Welcome Back
    </h2>

    <p
      style={{
        marginTop: "12px",
        color: "#1A5632",
        fontWeight: "700",
        fontSize: "18px",
      }}
    >
      {phone}
    </p>

    <p
      style={{
        color: "#6B7280",
        lineHeight: "24px",
      }}
    >
      Your Kin account is
      ready to use.
    </p>

    <button
      onClick={onContinue}
      style={{
        width: "100%",
        height: "58px",
        marginTop: "20px",
        border: "none",
        borderRadius: "18px",
        background:
          "linear-gradient(90deg,#1A5632,#3A7D44)",
        color: "#FFFFFF",
        fontWeight: "700",
        fontSize: "16px",
        cursor: "pointer",
      }}
    >
      Enter PIN
    </button>

    <button
      onClick={onClose}
      style={{
        width: "100%",
        height: "54px",
        marginTop: "12px",
        border: "none",
        background: "transparent",
        color: "#6B7280",
        cursor: "pointer",
      }}
    >
      Cancel
    </button>
  </div>
</div>

);
}

export default NumberRegisteredPopup;

