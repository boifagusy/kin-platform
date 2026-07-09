import { FaShieldAlt, FaBell } from "react-icons/fa";

function CheckInHeader({ userName, greeting }) {
  return (
    <div style={{ background: "white", padding: "16px 20px", borderBottom: "1px solid #e5e7eb", position: "sticky", top: 0, zIndex: 10 }}>
      <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between" }}>
        <FaShieldAlt style={{ fontSize: 22, color: "#1A5632" }} />
        <h1 style={{ fontSize: 22, fontWeight: "bold", color: "#1A5632", margin: 0 }}>KIN</h1>
        <FaBell style={{ fontSize: 22, color: "#1A5632" }} />
      </div>
      <div style={{ marginTop: 12 }}>
        <p style={{ color: "#6b7280", fontSize: 14, margin: 0 }}>Good {greeting},</p>
        <h2 style={{ color: "#1A5632", fontSize: 28, fontWeight: "bold", margin: "4px 0 0 0" }}>{userName}</h2>
        <div style={{ display: "flex", alignItems: "center", gap: 8, marginTop: 8 }}>
          <div style={{ width: 8, height: 8, borderRadius: "50%", background: "#22c55e" }}></div>
          <span style={{ color: "#6b7280", fontSize: 12 }}>Connected • SMS Available</span>
        </div>
      </div>
    </div>
  );
}

export default CheckInHeader;
