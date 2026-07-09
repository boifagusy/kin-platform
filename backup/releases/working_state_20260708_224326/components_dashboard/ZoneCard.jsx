import { FaMapMarkerAlt } from "react-icons/fa";

function ZoneCard() {
  return (
    <div className="bg-white rounded-2xl p-4 shadow-sm flex items-center gap-3">
      <div className="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
        <FaMapMarkerAlt className="text-gray-500" size={18} />
      </div>
      <div>
        <p className="font-semibold text-[#1A5632] text-sm">Passive Safe Zones Active</p>
        <p className="text-xs text-gray-500 mt-0.5">Home • Work • Gym</p>
      </div>
    </div>
  );
}

export default ZoneCard;
