import { FaClock, FaUsers } from "react-icons/fa";

function CheckInStats({ nextCheckin, contactsCount }) {
  return (
    <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 16 }}>
      <div style={{ background: "white", borderRadius: 24, padding: 20, boxShadow: "0 1px 3px rgba(0,0,0,0.1)" }}>
        <div style={{ width: 40, height: 40, borderRadius: 20, background: "#e8f5e9", display: "flex", alignItems: "center", justifyContent: "center", marginBottom: 12 }}>
          <FaClock style={{ fontSize: 20, color: "#1A5632" }} />
        </div>
        <p style={{ color: "#6b7280", fontSize: 12, margin: 0 }}>Next Check-in</p>
        <p style={{ color: "#1A5632", fontSize: 20, fontWeight: "bold", margin: "4px 0 0 0" }}>{nextCheckin}</p>
      </div>
      <div style={{ background: "white", borderRadius: 24, padding: 20, boxShadow: "0 1px 3px rgba(0,0,0,0.1)" }}>
        <div style={{ width: 40, height: 40, borderRadius: 20, background: "#fef3c7", display: "flex", alignItems: "center", justifyContent: "center", marginBottom: 12 }}>
          <FaUsers style={{ fontSize: 20, color: "#D4A017" }} />
        </div>
        <p style={{ color: "#6b7280", fontSize: 12, margin: 0 }}>Trusted Contacts</p>
        <p style={{ color: "#1A5632", fontSize: 20, fontWeight: "bold", margin: "4px 0 0 0" }}>{contactsCount} Active</p>
      </div>
    </div>
  );
}

export default CheckInStats;
