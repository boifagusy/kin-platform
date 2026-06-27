import React from "react";
import BaseModal from "./BaseModal";

function AccountFoundModal({ open, phoneNumber, onEnterPin, onChangeNumber }) {
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
      onConfirm={onEnterPin}
      onCancel={onChangeNumber}
      title="Welcome Back"
      phoneNumber={formatPhoneNumber(phoneNumber)}
      subtitle="We found your KIN account. Your safety network is ready."
      confirmText="Enter PIN"
      cancelText="Not you? Change number"
      variant="existing"
      showCloseIcon={true}
    />
  );
}

export default AccountFoundModal;
