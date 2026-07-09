import React from 'react';
import { FaShieldAlt } from "react-icons/fa";

const SafetyCircleCard = () => {
  return (
    <div style={{ borderRadius: "20px", background: "#EDF5F0", border: "2px solid #1A5632", padding: "20px", marginBottom: "36px" }}>
      <div style={{ display: "flex", alignItems: "center", gap: "10px", marginBottom: "12px" }}>
        <FaShieldAlt size={24} color="#1A5632" />
        <h3 style={{ fontWeight: "700", color: "#1A5632", fontSize: "18px" }}>Safety Circle</h3>
      </div>
      <ul style={{ color: "#374151", fontSize: "14px", lineHeight: "24px", paddingLeft: "20px" }}>
        <li>✓ Missed Check-In alerts</li>
        <li>✓ SOS alerts</li>
        <li>✓ Emergency notifications</li>
      </ul>
    </div>
  );
};

export default SafetyCircleCard;
