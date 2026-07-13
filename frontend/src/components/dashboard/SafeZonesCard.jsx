import { FaMapMarkerAlt, FaPlus } from "react-icons/fa";
import { useNavigate } from "react-router-dom";

function SafeZonesCard({ zones = [], count = 0, defaultZone = null }) {
  const navigate = useNavigate();

  return (
    <div style={{ background: "white", borderRadius: 20, padding: 20, marginBottom: 16, boxShadow: "0 1px 3px rgba(0,0,0,0.1)" }}>
      <div style={{ display: "flex", alignItems: "center", gap: 14 }}>
        <div style={{ width: 48, height: 48, borderRadius: 24, background: "#e8f5e9", display: "flex", alignItems: "center", justifyContent: "center", flexShrink: 0 }}>
          <FaMapMarkerAlt style={{ fontSize: 24, color: "#1A5632" }} />
        </div>
        <div style={{ flex: 1, minWidth: 0 }}>
          {count === 0 ? (
            <>
              <p style={{ fontWeight: 600, color: "#1A5632", margin: 0, fontSize: 14 }}>No Safe Zones</p>
              <p style={{ color: "#6b7280", fontSize: 12, margin: "2px 0 0 0" }}>Add a safe zone in Settings</p>
            </>
          ) : (
            <>
              <p style={{ fontWeight: 600, color: "#1A5632", margin: 0, fontSize: 14 }}>Safe Zones ({count})</p>
              <p style={{ color: "#6b7280", fontSize: 12, margin: "2px 0 0 0", overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>
                {zones.map(z => z.name).join(" • ")}
              </p>
            </>
          )}
        </div>
        <button
          onClick={() => navigate('/settings/safe-zones')}
          style={{
            width: 40, height: 40, borderRadius: 20,
            background: "#1A5632", border: "none",
            display: "flex", alignItems: "center", justifyContent: "center",
            cursor: "pointer", flexShrink: 0,
            minWidth: 40, minHeight: 40
          }}
          aria-label="Add safe zone"
        >
          <FaPlus style={{ color: "white", fontSize: 18 }} />
        </button>
      </div>
    </div>
  );
}

export default SafeZonesCard;
