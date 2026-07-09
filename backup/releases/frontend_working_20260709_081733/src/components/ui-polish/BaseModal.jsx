import React from "react";

function BaseModal({ 
  open, 
  onClose, 
  onConfirm, 
  onCancel, 
  title, 
  subtitle, 
  phoneNumber, 
  message, 
  confirmText, 
  cancelText = "Cancel",
  showCloseIcon = true,
  variant = "default",
  children 
}) {
  if (!open) return null;

  const handleConfirm = () => {
    if (onConfirm) onConfirm();
  };

  const handleCancel = () => {
    if (onCancel) onCancel();
    if (onClose) onClose();
  };

  const getIcon = () => {
    switch (variant) {
      case "existing":
        return "shield";
      case "new":
        return "person_add";
      default:
        return "security";
    }
  };

  const getIconBg = () => {
    switch (variant) {
      case "existing":
        return "from-[#1A5632] to-[#0E3A22]";
      case "new":
        return "from-[#D4A017] to-[#E0B833]";
      default:
        return "from-[#1A5632] to-[#0E3A22]";
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center px-4 bg-black/50 backdrop-blur-sm animate-fadeIn">
      <div className="relative w-full max-w-md bg-white rounded-2xl shadow-2xl animate-scaleIn overflow-hidden">
        
        {/* Close Icon - Top Right */}
        {showCloseIcon && (
          <button
            onClick={handleCancel}
            className="absolute top-4 right-4 text-[#6C757D] hover:text-[#1A5632] transition-colors z-10"
          >
            <span className="material-symbols-outlined text-2xl">close</span>
          </button>
        )}

        {/* Content */}
        <div className="p-6 text-center">
          {/* Icon */}
          <div className={`w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-br ${getIconBg()} shadow-lg flex items-center justify-center`}>
            <span className="material-symbols-outlined text-white text-3xl" style={{ fontVariationSettings: "'FILL' 1" }}>
              {getIcon()}
            </span>
          </div>

          {/* Title */}
          <h2 className="text-2xl font-bold text-[#1A1A1A]">
            {title}
          </h2>

          {/* Phone Number */}
          {phoneNumber && (
            <p className="mt-2 text-lg font-semibold text-[#1A5632]">
              {phoneNumber}
            </p>
          )}

          {/* Message */}
          {subtitle && (
            <p className="mt-2 text-sm text-[#6C757D]">
              {subtitle}
            </p>
          )}
          
          {message && !subtitle && (
            <p className="mt-2 text-sm text-[#6C757D]">
              {message}
            </p>
          )}

          {children}

          {/* Buttons */}
          <div className="mt-6 space-y-3">
            {onConfirm && (
              <button
                onClick={handleConfirm}
                className="w-full h-12 rounded-xl bg-gradient-to-r from-[#1A5632] to-[#0E3A22] text-white font-semibold text-base shadow-md hover:shadow-lg transition-all active:scale-95"
              >
                {confirmText || "Continue"}
              </button>
            )}
            <button
              onClick={handleCancel}
              className="w-full py-2 text-sm font-medium text-[#6C757D] hover:text-[#1A5632] transition-colors"
            >
              {cancelText}
            </button>
          </div>
        </div>
      </div>

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

export default BaseModal;
