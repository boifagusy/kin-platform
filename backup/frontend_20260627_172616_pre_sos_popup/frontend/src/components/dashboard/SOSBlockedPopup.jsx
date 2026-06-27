import React from 'react';
import { FaLock, FaUserPlus, FaTimes } from 'react-icons/fa';
import { useNavigate } from 'react-router-dom';

const SOSBlockedPopup = ({ isOpen, onClose, reason }) => {
  const navigate = useNavigate();

  if (!isOpen) return null;

  const handleAddContact = () => {
    onClose();
    navigate("/trusted-contacts");
  };

  return (
    <div className="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
      <div className="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden animate-scale-in">
        {/* Header */}
        <div className="bg-[#1A5632] px-6 py-4 flex items-center gap-3">
          <div className="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
            <FaLock className="text-white text-xl" />
          </div>
          <h2 className="text-white text-lg font-bold">🔒 SOS is Locked</h2>
        </div>

        {/* Body */}
        <div className="p-6">
          <p className="text-[#374151] text-sm leading-relaxed mb-4">
            {reason || "To use SOS, you need at least one trusted contact who has verified their connection."}
          </p>

          <div className="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5">
            <p className="text-amber-800 text-xs font-medium">
              💡 Tip: Your trusted contacts receive alerts when you trigger SOS.
              Add and verify a contact to activate this feature.
            </p>
          </div>

          <div className="space-y-2.5">
            <button
              onClick={handleAddContact}
              className="w-full h-12 rounded-xl bg-gradient-to-r from-[#1A5632] to-[#0E3A22] text-white font-semibold text-base shadow-lg shadow-[#1A5632]/20 hover:opacity-90 active:scale-95 transition-all flex items-center justify-center gap-2"
            >
              <FaUserPlus />
              Add Trusted Contact
            </button>

            <button
              onClick={onClose}
              className="w-full h-11 rounded-xl bg-gray-100 text-gray-700 font-medium text-sm hover:bg-gray-200 active:scale-95 transition-all flex items-center justify-center gap-2"
            >
              <FaTimes />
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default SOSBlockedPopup;
