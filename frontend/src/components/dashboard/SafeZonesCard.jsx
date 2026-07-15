import { FaMapMarkerAlt, FaPlus } from "react-icons/fa";
import { useNavigate } from "react-router-dom";

function SafeZonesCard({ zones = [], count = 0, defaultZone = null }) {
  const navigate = useNavigate();

  return (
    <div className="bg-white rounded-2xl p-4 shadow-sm border border-[#E9ECEF] mb-4">
      <div className="flex items-center justify-between mb-3">
        <div>
          <h3 className="text-sm font-semibold text-[#1A1A1A]">📍 Safe Zones</h3>
          <p className="text-xs text-[#6C757D] mt-0.5">Trusted places for emergency response</p>
        </div>
        <button
          onClick={() => navigate('/settings/safe-zones')}
          className="w-8 h-8 rounded-full bg-[#1A5632] flex items-center justify-center active:scale-95 transition-all flex-shrink-0"
          aria-label="Add safe zone"
        >
          <FaPlus style={{ color: "white", fontSize: 14 }} />
        </button>
      </div>

      {zones.length === 0 ? (
        <div className="text-center py-4">
          <FaMapMarkerAlt className="text-gray-300 text-2xl mx-auto mb-2" />
          <p className="text-xs text-[#6C757D]">No safe zones added yet</p>
        </div>
      ) : (
        <div className="space-y-2">
          {zones.map((zone) => (
            <div key={zone.id} className="flex items-center gap-3 p-2 rounded-xl bg-[#F8F9FA]">
              <div className="w-8 h-8 rounded-full bg-[#E8F3EA] flex items-center justify-center flex-shrink-0">
                <FaMapMarkerAlt style={{ fontSize: 14, color: "#1A5632" }} />
              </div>
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-[#1A1A1A] truncate">{zone.name}</p>
              </div>
              {zone.is_default && (
                <span className="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium flex-shrink-0">
                  Default
                </span>
              )}
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

export default SafeZonesCard;
