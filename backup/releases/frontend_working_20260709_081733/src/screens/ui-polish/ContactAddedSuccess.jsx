import { useNavigate, useLocation } from "react-router-dom";
import { FaCheckCircle, FaShareAlt } from "react-icons/fa";

function ContactAddedSuccess() {
  const navigate = useNavigate();
  const location = useLocation();
  const contactName = location.state?.contactName || "Contact";
  const contactPhone = location.state?.contactPhone || "";
  const phone = location.state?.phone || "";

  const inviteLink = `https://kin.app/invite?ref=${phone}`;

  const handleShare = async () => {
    const shareData = {
      title: "Join my KIN safety network",
      text: `${contactName}, join my trusted safety network on KIN.`,
      url: inviteLink,
    };

    // Try native share first (mobile)
    if (navigator.share) {
      try {
        await navigator.share(shareData);
        return;
      } catch (err) {
        if (err.name !== "AbortError") {
          console.error("Share error:", err);
          fallbackCopy();
        }
      }
    } else {
      // Fallback: copy to clipboard
      fallbackCopy();
    }
  };

  const fallbackCopy = () => {
    navigator.clipboard.writeText(inviteLink).then(() => {
      alert("✅ Invite link copied! Share it with your contact.");
    }).catch(() => {
      // Final fallback: show the link
      prompt("Copy this link to share:", inviteLink);
    });
  };

  const handleContinue = () => {
    navigate("/dashboard");
  };

  return (
    <div className="min-h-screen bg-[#F0F7F2] flex items-center justify-center px-6">
      <div className="w-full max-w-sm text-center">
        {/* Icon */}
        <div className="w-24 h-24 mx-auto mb-6 rounded-full bg-green-100 flex items-center justify-center">
          <FaCheckCircle className="text-5xl text-green-600" />
        </div>

        {/* Title */}
        <h1 className="text-2xl font-bold text-[#1A1A1A] mb-2">
          Contact Added
        </h1>

        {/* Description */}
        <p className="text-sm text-[#6C757D] mb-4">
          {contactName} is now part of your trusted network.
        </p>

        {/* Phone */}
        {contactPhone && (
          <p className="text-sm font-medium text-[#1A5632] mb-6">
            📱 {contactPhone}
          </p>
        )}

        <div className="border-t border-[#E9ECEF] pt-6 mb-6">
          <p className="text-sm text-[#1A1A1A] font-medium mb-2">
            Invite {contactName} to join KIN
          </p>
        </div>

        {/* Share Button */}
        <button
          onClick={handleShare}
          className="w-full h-14 rounded-2xl bg-gradient-to-r from-[#1A5632] to-[#0E3A22] text-white font-bold text-base shadow-lg shadow-[#1A5632]/20 flex items-center justify-center gap-3 active:scale-95 transition-all"
        >
          <FaShareAlt className="text-lg" />
          Share Invite
        </button>

        {/* Continue Button */}
        <button
          onClick={handleContinue}
          className="w-full h-14 rounded-2xl bg-transparent text-[#1A5632] font-semibold text-base active:scale-95 transition-all mt-4"
        >
          Continue
        </button>
      </div>
    </div>
  );
}

export default ContactAddedSuccess;
