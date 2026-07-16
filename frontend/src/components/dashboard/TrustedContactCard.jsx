import { useNavigate } from "react-router-dom";
import { FaUserPlus, FaShareAlt, FaUser, FaSync } from "react-icons/fa";

function TrustedContactCard({ contact, inviteStatus, onShare, onReplace }) {
  const navigate = useNavigate();


  // STATE A: No trusted contact
  if (!contact || !contact.name) {
    return (
      <div className="bg-white rounded-2xl p-4 shadow-sm border border-[#E9ECEF]">
        <h3 className="text-sm font-semibold text-[#1A1A1A] mb-1">Trusted Network</h3>
        <p className="text-xs text-[#6C757D] mb-3">No trusted contacts yet</p>
        <button
          onClick={() => navigate("/network")}
          className="w-full h-10 rounded-xl bg-[#1A5632] text-white font-semibold text-sm flex items-center justify-center gap-2 active:scale-95 transition-all"
        >
          <FaUserPlus />
          Add Contact
        </button>
      </div>
    );
  }

  // STATE B: Contact exists
  const statusLabel = inviteStatus === "required" ? "Invite Required" : 
                      inviteStatus === "waiting" ? "Waiting For Acceptance" : 
                      "Active ✓";
  const statusIcon = inviteStatus === "required" ? "📤" : 
                     inviteStatus === "waiting" ? "⏳" : 
                     "✅";

  return (
    <div className="bg-white rounded-2xl p-4 shadow-sm border border-[#E9ECEF]">
      <div className="flex items-center justify-between">
        <div>
          <h3 className="text-sm font-semibold text-[#1A1A1A]">Trusted Network</h3>
          <div className="flex items-center gap-2 mt-1">
            <div className="w-8 h-8 rounded-full bg-[#E8F3EA] flex items-center justify-center">
              <FaUser className="text-[#1A5632] text-sm" />
            </div>
            <div>
              <p className="text-sm font-medium text-[#1A1A1A]">{contact.name}</p>
              <p className="text-xs text-[#6C757D] flex items-center gap-1">
                <span>{statusIcon}</span> {statusLabel}
              </p>
            </div>
          </div>
        </div>
        <span className="text-xs text-[#6C757D]">📱 {contact.phone}</span>
      </div>

      <div className="flex gap-2 mt-3">
        <button
          onClick={onShare}
          className="flex-1 h-9 rounded-xl bg-[#1A5632] text-white font-medium text-xs flex items-center justify-center gap-2 active:scale-95 transition-all"
        >
          <FaShareAlt size={12} />
          Share Invite
        </button>
        <button
          onClick={onReplace}
          className="flex-1 h-9 rounded-xl bg-gray-100 text-[#6C757D] font-medium text-xs flex items-center justify-center gap-2 active:scale-95 transition-all"
        >
          <FaSync size={12} />
          Replace
        </button>
      </div>
    </div>
  );
}

export default TrustedContactCard;
