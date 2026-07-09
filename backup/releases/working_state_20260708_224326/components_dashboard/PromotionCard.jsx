import { FaGift } from "react-icons/fa";

function PromotionCard({ title, description }) {
  return (
    <div style={{ background: "linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%)", borderRadius: 24, padding: 16, marginBottom: 16, border: "1px solid #fde68a", position: "relative" }}>
      <div style={{ position: "absolute", top: 12, right: 12, background: "#D4A017", color: "#78350f", fontSize: 10, fontWeight: "bold", padding: "2px 8px", borderRadius: 12 }}>OFFER</div>
      <div style={{ display: "flex", alignItems: "center", gap: 12 }}>
        <div style={{ width: 48, height: 48, borderRadius: 24, background: "#1A5632", display: "flex", alignItems: "center", justifyContent: "center" }}>
          <FaGift style={{ fontSize: 20, color: "white" }} />
        </div>
        <div>
          <p style={{ fontWeight: "bold", fontSize: 14, margin: 0 }}>{title}</p>
          <p style={{ fontSize: 11, color: "#6b7280", margin: "4px 0 0 0" }}>{description}</p>
        </div>
      </div>
    </div>
  );
}

export default PromotionCard;
