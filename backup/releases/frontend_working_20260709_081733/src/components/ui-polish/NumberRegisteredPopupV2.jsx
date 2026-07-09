import React from "react";

function NumberRegisteredPopupV2({ open, phone, onContinue, onClose }) {
  if (!open) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center px-6 bg-black/50 backdrop-blur-sm animate-fadeIn">
      <div className="w-full max-w-sm bg-white rounded-2xl shadow-2xl p-6 text-center animate-scaleIn">
        {/* Icon */}
        <div className="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-br from-[#1A5632] to-[#0E3A22] shadow-lg flex items-center justify-center">
          <span className="material-symbols-outlined text-white text-3xl" style={{ fontVariationSettings: "'FILL' 1" }}>
            shield
          </span>
        </div>

        {/* Title */}
        <h2 className="text-2xl font-bold text-[#1A1A1A]">
          Welcome Back
        </h2>

        {/* Phone number */}
        <p className="mt-2 text-lg font-semibold text-[#1A5632]">
          {phone}
        </p>

        {/* Message */}
        <p className="mt-2 text-sm text-[#6C757D]">
          Your Kin account is ready to use.
        </p>

        {/* Buttons */}
        <div className="mt-6 space-y-3">
          <button
            onClick={onContinue}
            className="w-full h-12 rounded-xl bg-gradient-to-r from-[#1A5632] to-[#0E3A22] text-white font-semibold text-base shadow-md hover:shadow-lg transition-all active:scale-95"
          >
            Enter PIN
          </button>
          <button
            onClick={onClose}
            className="w-full py-2 text-sm font-medium text-[#6C757D] hover:text-[#1A5632] transition-colors"
          >
            Cancel
          </button>
        </div>
      </div>

      {/* Animations */}
      <style>{`
        @keyframes fadeIn {
          from { opacity: 0; }
          to { opacity: 1; }
        }
        @keyframes scaleIn {
          from { transform: scale(0.95); opacity: 0; }
          to { transform: scale(1); opacity: 1; }
        }
        .animate-fadeIn {
          animation: fadeIn 0.2s ease-out;
        }
        .animate-scaleIn {
          animation: scaleIn 0.2s ease-out;
        }
      `}</style>
    </div>
  );
}

export default NumberRegisteredPopupV2;
