import { useNavigate } from "react-router-dom";
import { FaUserPlus, FaShareAlt, FaUser, FaSync, FaCheck, FaTimes } from "react-icons/fa";
import { useState } from "react";

function TrustedContactCard({ contact, inviteStatus, onShare, onReplace, pendingInvitations = [], onApprove, onReject }) {
  const navigate = useNavigate();
  const [approving, setApproving] = useState(null);
  const [rejecting, setRejecting] = useState(null);

  const handleApprove = async (id) => {
    if (onApprove) {
      setApproving(id);
      try {
        await onApprove(id);
      } finally {
        setApproving(null);
      }
    }
  };

  const handleReject = async (id) => {
    if (onReject) {
      setRejecting(id);
      try {
        await onReject(id);
      } finally {
        setRejecting(null);
      }
    }
  };

  // STATE C: Pending invitations received by this user
  if (pendingInvitations && pendingInvitations.length > 0) {
    return (
      <div className="space-y-3">
        {pendingInvitations.map((invite) => (
          <div key={invite.id} className="bg-blue-50 rounded-2xl p-4 border border-blue-200">
            <div className="flex items-start justify-between mb-3">
              <div className="flex items-center gap-2">
                <div className="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                  <FaUser className="text-blue-600 text-sm" />
                </div>
                <div>
                  <p className="text-sm font-semibold text-[#1A1A1A]">{invite.name}</p>
                  <p className="text-xs text-[#6C757D]">📱 {invite.phone}</p>
                </div>
              </div>
              <span className="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full">
                Pending Your Action
              </span>
            </div>
            <p className="text-xs text-[#6C757D] mb-3">
              {invite.name} wants to add you as a trusted contact
            </p>
            <div className="flex gap-2">
              <button
                onClick={() => handleApprove(invite.id)}
                disabled={approving === invite.id}
                className="flex-1 h-9 rounded-xl bg-[#1A5632] text-white font-medium text-xs flex items-center justify-center gap-2 active:scale-95 transition-all disabled:opacity-60"
              >
                <FaCheck size={12} />
                {approving === invite.id ? "Approving..." : "Approve"}
              </button>
              <button
                onClick={() => handleReject(invite.id)}
                disabled={rejecting === invite.id}
                className="flex-1 h-9 rounded-xl bg-red-100 text-red-600 font-medium text-xs flex items-center justify-center gap-2 active:scale-95 transition-all disabled:opacity-60"
              >
                <FaTimes size={12} />
                {rejecting === invite.id ? "Rejecting..." : "Reject"}
              </button>
            </div>
          </div>
        ))}
      </div>
    );
  }

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
