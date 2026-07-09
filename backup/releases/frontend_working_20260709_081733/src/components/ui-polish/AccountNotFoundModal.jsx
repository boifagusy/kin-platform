import React from "react";
import BaseModal from "./BaseModal";

function AccountNotFoundModal({ open, phoneNumber, onContinue, onChangeNumber }) {
  const formatPhoneNumber = (phone) => {
    if (!phone) return "";
    if (phone.length === 14) {
      return `${phone.slice(0, 7)} XXX ${phone.slice(-3)}`;
    }
    return phone;
  };

  return (
    <BaseModal
      open={open}
      onConfirm={onContinue}
      onCancel={onChangeNumber}
      title="Create Your Safety Network"
      phoneNumber={formatPhoneNumber(phoneNumber)}
      subtitle="No account was found for this number. Let's create your safety network."
      confirmText="Continue"
      cancelText="Change Number"
      variant="new"
      showCloseIcon={true}
    />
  );
}

export default AccountNotFoundModal;
