import { useState, useEffect, useCallback } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import LoadingScreen from "../../components/ui/LoadingScreen";
import { saveTrustedContact, getTrustedContacts } from "../../services/api";
import ProgressIndicator from "../../components/trusted-contact/ProgressIndicator";
import SafetyCircleCard from "../../components/trusted-contact/SafetyCircleCard";

// Constants
const PHONE_REGEX = /^[0-9]{10,14}$/;
const MIN_NAME_LENGTH = 2;

function TrustedContactScreenV2() {
  const navigate = useNavigate();
  const location = useLocation();
  const phone = location.state?.phone;
  const fullName = location.state?.full_name || "User";
  const [contactName, setContactName] = useState("");
  const [contactPhone, setContactPhone] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [contactAdded, setContactAdded] = useState(false);
  const [existingContacts, setExistingContacts] = useState([]);
  const [isSubmitting, setIsSubmitting] = useState(false);

  // Validate inputs
  const isNameValid = contactName.trim().length >= MIN_NAME_LENGTH;
  const isPhoneValid = PHONE_REGEX.test(contactPhone.replace(/[^0-9]/g, ''));
  const canContinue = isNameValid && isPhoneValid && !isSubmitting;

  // Load existing contacts
  useEffect(() => {
    const loadContacts = async () => {
      try {
        const contacts = await getTrustedContacts();
        console.log("🔍 Contacts API response:", contacts);
        setExistingContacts(contacts || []);
      } catch (error) {
        console.error('Failed to load contacts:', error);
      }
    };
    loadContacts();
  }, []);

  // Check for duplicate
  const isDuplicate = useCallback(() => {
    const cleanPhone = contactPhone.replace(/[^0-9]/g, '');
    return existingContacts.some(c =>
      c.contact_phone?.replace(/[^0-9]/g, '') === cleanPhone
    );
  }, [contactPhone, existingContacts]);

  // Generate secure verification token
  const generateSecureToken = useCallback(() => {
    const array = new Uint8Array(32);
    crypto.getRandomValues(array);
    return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
  }, []);

  // Save contact to backend
  const saveContactToBackend = async () => {
    const cleanPhone = contactPhone.replace(/[^0-9]/g, '');

    if (isDuplicate()) {
      throw new Error('This contact is already in your safety circle.');
    }

    const contactData = {
      phone,
      contact_name: contactName.trim(),
      contact_phone: cleanPhone,
      invite_sent: false,
      verified: false,
    };

    return await saveTrustedContact(contactData);
  };

  // Handle add contact
  const handleAddContact = async () => {
    if (!canContinue || isSubmitting) return;

    setIsSubmitting(true);
    setError("");

    try {
      await saveContactToBackend();
      setContactAdded(true);

      console.log('✅ Trusted contact saved', {
        contact_name: contactName.trim(),
        timestamp: new Date().toISOString(),
      });
    } catch (err) {
      setError(err.message || "Failed to save contact. Please try again.");
      console.error('❌ Failed to save trusted contact:', err);
    } finally {
      setIsSubmitting(false);
      setLoading(false);
    }
  };

  // Generate invite message
  const getInviteMessage = useCallback(() => {
    const token = generateSecureToken();
    const baseUrl = import.meta.env.VITE_API_URL || 'https://api.kin.app';
    const appUrl = import.meta.env.VITE_APP_URL || 'https://kin.app';
    const verifyLink = `${baseUrl}/api/v1/trusted-contact/verify/${token}`;
    const downloadLink = `${appUrl}/download`;
    const contactPhoneClean = contactPhone.replace(/[^0-9]/g, '');

    return `Hi ${contactName.trim()},

${fullName} has added you as a trusted contact on KIN, a personal safety app. This means you may receive an alert if they miss a scheduled check-in or get alerted in case of emergency.

Please confirm you're willing to take on this role:
👉 Confirm as Contact: ${verifyLink}

Don't have KIN yet? Download it here:
📲 Get KIN: ${downloadLink}

Thanks for helping keep ${fullName} safe.
— The KIN Team`;
  }, [contactName, contactPhone, fullName, generateSecureToken]);

  // Share invite
  const handleShareInvite = useCallback(async () => {
    const message = getInviteMessage();
    const cleanPhone = contactPhone.replace(/[^0-9]/g, '');

    try {
      if (navigator.share && navigator.canShare?.({ text: message })) {
        await navigator.share({
          title: 'Join KIN Safety Network',
          text: message,
        });
        return;
      }

      const smsUrl = `sms:${cleanPhone}?body=${encodeURIComponent(message)}`;
      window.open(smsUrl, '_blank');
    } catch (error) {
      if (error.name !== 'AbortError') {
        console.error('Share failed:', error);
        setError('Failed to share invitation. Please copy the message manually.');
      }
    }
  }, [getInviteMessage, contactPhone]);

  const handleContinue = useCallback(() => {
    navigate("/checkin-settings", {
      state: {
        phone,
        full_name: fullName,
        trusted_contact: {
          name: contactName.trim(),
          phone: contactPhone.replace(/[^0-9]/g, ''),
        }
      }
    });
  }, [navigate, phone, fullName, contactName, contactPhone]);

  const handleBack = useCallback(() => {
    if (window.history.length > 1) {
      navigate(-1);
    } else {
      navigate("/onboarding");
    }
  }, [navigate]);

  return (
    <>
      <LoadingScreen open={loading} message="contacts" />

      <div className="fixed inset-0 bg-gradient-to-br from-[#F0F7F2] to-[#E8F3EA] flex flex-col">
        {/* Background orbs */}
        <div className="absolute top-[-100px] left-[-100px] w-80 h-80 bg-[#1A5632]/10 rounded-full blur-3xl pointer-events-none" />
        <div className="absolute bottom-[-100px] right-[-100px] w-80 h-80 bg-[#D4A017]/10 rounded-full blur-3xl pointer-events-none" />

        {/* Back button */}
        <button
          onClick={handleBack}
          className="absolute top-6 left-6 z-10 w-10 h-10 rounded-full bg-white/80 backdrop-blur-sm shadow-md flex items-center justify-center active:scale-95 transition-all"
          aria-label="Go back"
        >
          <span className="material-symbols-outlined text-[#1A5632] text-2xl">arrow_back</span>
        </button>

        <div className="flex-1 flex flex-col items-center justify-center px-6 py-4">
          <div className="w-full max-w-md">
            {/* Header */}
            <div className="text-center mb-3">
              <div className="w-12 h-12 mx-auto mb-2 rounded-full bg-gradient-to-br from-[#1A5632] to-[#0E3A22] shadow-lg flex items-center justify-center">
                <span className="material-symbols-outlined text-white text-xl">shield</span>
              </div>
              <h1 className="text-base font-black text-[#1A5632] tracking-[0.2em]">KIN</h1>
            </div>

            {/* Title */}
            <div className="text-center mt-4 mb-2">
              <h2 className="text-xl font-bold text-[#1A1A1A]">Contact</h2>
              <p className="text-[#6C757D] text-sm mt-1">Manage who KIN notifies during an emergency.</p>
            </div>

            {/* Conditional Rendering */}
            {existingContacts.length > 0 ? (
              <div className="mt-6">
                <h3 className="text-sm font-semibold text-[#1A1A1A] mb-3">Emergency Contact</h3>
                
                <div className="bg-white rounded-2xl p-6 shadow-sm border border-[#E9ECEF]">
                  <div className="flex items-center gap-4">
                    <div className="w-16 h-16 rounded-full bg-[#1A5632] text-white flex items-center justify-center text-2xl font-bold">
                      {existingContacts[0]?.contact_name?.charAt(0) || '?'}
                    </div>
                    <div>
                      <p className="text-lg font-semibold text-[#1A1A1A]">{existingContacts[0]?.contact_name || 'Unknown'}</p>
                      <p className="text-sm text-[#6C757D]">{existingContacts[0]?.contact_phone || 'No phone'}</p>
                      <span className="text-xs text-green-600 font-medium mt-1 inline-block">🟢 Active</span>
                    </div>
                  </div>
                </div>

                <div className="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-xl">
                  <p className="text-xs text-[#1A5632]">🔒 Contact changes are protected for 30 days.</p>
                </div>

                <div className="mt-4">
                  <p className="text-sm text-[#6C757D]">
                    Replacement available in <span className="font-semibold text-[#D4A017]">14 days</span>.
                  </p>
                  <button
                    disabled
                    className="w-full h-12 rounded-xl font-semibold text-base flex items-center justify-center gap-2 mt-3 bg-[#B7D4BF] text-white cursor-not-allowed"
                  >
                    🔒 Replace Contact
                  </button>
                </div>
              </div>
            ) : (
              <div className="mt-6">
                <div className="text-center mb-6">
                  <div className="w-20 h-20 mx-auto mb-3 rounded-full bg-[#F0F7F2] flex items-center justify-center">
                    <span className="material-symbols-outlined text-4xl text-[#6C757D]">person_add</span>
                  </div>
                  <h3 className="text-lg font-semibold text-[#1A1A1A]">No Contact Added</h3>
                  <p className="text-sm text-[#6C757D] mt-1">Add someone KIN can notify during an emergency.</p>
                </div>

                {/* Form */}
                <div>
                  <label className="block text-xs font-medium text-[#6C757D] mb-1">
                    Contact Name <span className="text-red-500">*</span>
                  </label>
                  <div className={`bg-white rounded-xl px-4 py-3 shadow-sm border ${
                    contactName && !isNameValid ? 'border-red-300' : 'border-[#E9ECEF]'
                  } flex items-center gap-3`}>
                    <span className="material-symbols-outlined text-[#1A5632] text-xl">person</span>
                    <input
                      type="text"
                      value={contactName}
                      onChange={(e) => setContactName(e.target.value)}
                      placeholder="Enter full name"
                      className="flex-1 border-none outline-none bg-transparent text-base placeholder:text-[#6C757D]"
                      aria-label="Trusted contact name"
                      maxLength={50}
                    />
                  </div>
                  {contactName && !isNameValid && (
                    <p className="text-red-500 text-xs mt-1">Name must be at least 2 characters</p>
                  )}
                </div>

                <div className="mt-3">
                  <label className="block text-xs font-medium text-[#6C757D] mb-1">
                    Phone Number <span className="text-red-500">*</span>
                  </label>
                  <div className={`bg-white rounded-xl px-4 py-3 shadow-sm border ${
                    contactPhone && !isPhoneValid ? 'border-red-300' : 'border-[#E9ECEF]'
                  } flex items-center gap-3`}>
                    <span className="material-symbols-outlined text-[#1A5632] text-xl">phone</span>
                    <input
                      type="tel"
                      value={contactPhone}
                      onChange={(e) => setContactPhone(e.target.value)}
                      placeholder="08012345678"
                      className="flex-1 border-none outline-none bg-transparent text-base placeholder:text-[#6C757D]"
                      aria-label="Trusted contact phone number"
                      maxLength={14}
                    />
                  </div>
                  {contactPhone && !isPhoneValid && (
                    <p className="text-red-500 text-xs mt-1">Enter a valid phone number (10-14 digits)</p>
                  )}
                  {isDuplicate() && (
                    <p className="text-amber-500 text-xs mt-1">⚠️ This contact is already in your safety circle</p>
                  )}
                </div>

                <div className="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-xl">
                  <p className="text-xs text-[#1A5632]">🔒 Contact changes are protected for 30 days.</p>
                </div>

                {error && (
                  <div className="mt-2 p-3 bg-red-50 border border-red-200 rounded-xl">
                    <p className="text-red-600 text-xs text-center">{error}</p>
                  </div>
                )}

                <button
                  disabled={!canContinue || isDuplicate()}
                  onClick={handleAddContact}
                  className={`w-full h-12 rounded-xl font-semibold text-base flex items-center justify-center gap-2 mt-4 transition-all ${
                    !canContinue || isDuplicate() || isSubmitting
                      ? "bg-[#B7D4BF] text-white cursor-not-allowed"
                      : "bg-gradient-to-r from-[#1A5632] to-[#0E3A22] text-white shadow-lg hover:opacity-95 active:scale-95"
                  }`}
                  aria-label="Add trusted contact"
                >
                  {isSubmitting ? (
                    <>
                      <span className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
                      Saving...
                    </>
                  ) : (
                    "Add Contact"
                  )}
                </button>
              </div>
            )}
          </div>
        </div>
      </div>
    </>
  );
}

export default TrustedContactScreenV2;
