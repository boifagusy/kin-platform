// Network Screen - Trusted Contacts Management
// Allows users to view, add, and remove trusted contacts
// Free plan: 1 contact limit (marketing adjustment - no upgrade banner)

import { useState, useEffect } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import { 
  FaArrowLeft, 
  FaUserCircle, 
  FaTrash, 
  FaPlus, 
  FaCheckCircle,
  FaExclamationTriangle,
  FaPhone,
  FaClock
} from "react-icons/fa";

function NetworkScreen() {
  const location = useLocation();
  const navigate = useNavigate();
  const phone = location.state?.phone || localStorage.getItem("kin_phone");
  
  // State management
  const [loading, setLoading] = useState(true);
  const [contacts, setContacts] = useState([]);
  const [limit, setLimit] = useState(1);
  const [used, setUsed] = useState(0);
  const [canAdd, setCanAdd] = useState(true);
  const [showAddModal, setShowAddModal] = useState(false);
  const [showRemoveConfirm, setShowRemoveConfirm] = useState(null);
  const [formData, setFormData] = useState({ name: "", contact_phone: "" });
  const [formError, setFormError] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const [removalCooldownDays, setRemovalCooldownDays] = useState(30);
  
  // Fetch trusted contacts on load
  useEffect(() => {
    if (!phone) {
      navigate("/login");
      return;
    }
    fetchContacts();
  }, [phone]);
  
  const fetchContacts = async () => {
    try {
      const response = await fetch(`${API_BASE}/trusted-contacts?phone=${encodeURIComponent(phone)}`);
      const data = await response.json();
      
      if (data.success) {
        setContacts(data.data.contacts);
        setLimit(data.data.limit);
        setUsed(data.data.used);
        setCanAdd(data.data.can_add);
        setRemovalCooldownDays(data.data.removal_cooldown_days || 30);
      }
    } catch (error) {
      console.error("Error fetching contacts:", error);
    } finally {
      setLoading(false);
    }
  };
  
  // Validate phone number format
  const validatePhoneNumber = (phoneNumber) => {
    const cleaned = phoneNumber.replace(/\D/g, '');
    const isValid = cleaned.length === 10 || cleaned.length === 13;
    if (!isValid) {
      return "Invalid phone number. Use format: 08012345678 or +2348012345678";
    }
    return null;
  };
  
  // Add new trusted contact
  const addContact = async () => {
    if (!formData.name.trim()) {
      setFormError("Name is required");
      return;
    }
    if (!formData.contact_phone.trim()) {
      setFormError("Phone number is required");
      return;
    }
    
    const phoneError = validatePhoneNumber(formData.contact_phone);
    if (phoneError) {
      setFormError(phoneError);
      return;
    }
    
    setSubmitting(true);
    setFormError("");
    
    try {
      const response = await fetch(`${import.meta.env.VITE_API_URL}/trusted-contacts", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          phone: phone,
          name: formData.name,
          contact_phone: formData.contact_phone,
        }),
      });
      
      const data = await response.json();
      
      if (data.success) {
        setShowAddModal(false);
        setFormData({ name: "", contact_phone: "" });
        fetchContacts();
      } else {
        setFormError(data.message || "Failed to add contact");
      }
    } catch (error) {
      console.error("Add contact error:", error);
      setFormError("Network error. Please try again.");
    } finally {
      setSubmitting(false);
    }
  };
  
  // Remove trusted contact
  const removeContact = async (contactId) => {
    try {
      const response = await fetch(`${API_BASE}/trusted-contacts/${contactId}`, {
        method: "DELETE",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ phone: phone }),
      });
      
      const data = await response.json();
      
      if (data.success) {
        setShowRemoveConfirm(null);
        fetchContacts();
      } else {
        alert(data.message || "Failed to remove contact");
      }
    } catch (error) {
      console.error("Remove contact error:", error);
      alert("Network error. Please try again.");
    }
  };
  
  if (loading) {
    return (
      <div className="min-h-screen bg-[#F0F7F2] flex items-center justify-center">
        <div className="text-center">
          <div className="w-10 h-10 border-3 border-[#1A5632] border-t-transparent rounded-full animate-spin mx-auto mb-3" />
          <p className="text-[#1A5632] text-sm">Loading trusted contacts...</p>
        </div>
      </div>
    );
  }
  
  return (
    <div className="min-h-screen bg-[#F0F7F2] pb-24">
      
      {/* Header */}
      <div className="bg-white px-5 py-4 border-b border-gray-100 sticky top-0 z-10">
        <div className="flex items-center gap-4">
          <button onClick={() => navigate(-1)} className="cursor-pointer">
            <FaArrowLeft className="text-[#1A5632] text-xl" />
          </button>
          <h1 className="text-xl font-bold text-[#1A5632]">Trusted Contacts</h1>
        </div>
      </div>
      
      <div className="px-4 py-5 space-y-4 max-w-md mx-auto">
        
        {/* Contacts List */}
        {contacts.length === 0 ? (
          // Empty state
          <div className="bg-white rounded-2xl p-8 text-center shadow-sm">
            <div className="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
              <FaUserCircle className="text-gray-400 text-4xl" />
            </div>
            <h3 className="font-semibold text-gray-800 text-lg mb-2">No trusted contacts yet</h3>
            <p className="text-sm text-gray-500 mb-6">Add someone you trust to receive safety alerts</p>
            <button
              onClick={() => setShowAddModal(true)}
              className="bg-[#1A5632] text-white py-3 px-6 rounded-xl font-semibold flex items-center justify-center gap-2 mx-auto hover:bg-[#2F6A44] transition"
            >
              <FaPlus /> Add Trusted Contact
            </button>
          </div>
        ) : (
          <>
            {/* Contact Cards */}
            {contacts.map((contact) => (
              <div key={contact.id} className="bg-white rounded-2xl p-4 shadow-sm border-l-4 border-[#1A5632]">
                <div className="flex items-start gap-3">
                  <div className="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                    <FaUserCircle className="text-[#1A5632] text-2xl" />
                  </div>
                  <div className="flex-1">
                    <h3 className="font-semibold text-gray-800 text-base">{contact.name}</h3>
                    <p className="text-sm text-gray-500 flex items-center gap-1 mt-1">
                      <FaPhone className="text-xs text-gray-400" /> {contact.phone}
                    </p>
                    <div className="flex items-center gap-3 mt-2">
                      {contact.verified ? (
                        <div className="flex items-center gap-1">
                          <FaCheckCircle className="text-green-500 text-xs" />
                          <span className="text-xs text-green-600">Verified</span>
                        </div>
                      ) : (
                        <div className="flex items-center gap-1">
                          <FaExclamationTriangle className="text-yellow-500 text-xs" />
                          <span className="text-xs text-yellow-600">Pending</span>
                        </div>
                      )}
                      {contact.active && (
                        <span className="text-xs text-gray-400">• Active</span>
                      )}
                    </div>
                    {/* Show removal cooldown if cannot remove */}
                    {contact.can_remove === false && (
                      <div className="flex items-center gap-1 mt-2">
                        <FaClock className="text-orange-500 text-xs" />
                        <span className="text-xs text-orange-600">
                          Can remove in {contact.days_until_removal} days
                        </span>
                      </div>
                    )}
                  </div>
                  <button
                    onClick={() => {
                      if (contact.can_remove) {
                        setShowRemoveConfirm(contact);
                      } else {
                        alert(`Cannot remove this contact for ${contact.days_until_removal} more days. This prevents abuse.`);
                      }
                    }}
                    className={`w-8 h-8 rounded-full flex items-center justify-center transition ${
                      contact.can_remove 
                        ? 'bg-red-50 hover:bg-red-100 cursor-pointer' 
                        : 'bg-gray-100 cursor-not-allowed opacity-50'
                    }`}
                  >
                    <FaTrash className={`text-sm ${contact.can_remove ? 'text-red-500' : 'text-gray-400'}`} />
                  </button>
                </div>
              </div>
            ))}
            
            {/* Add Contact Button - Show only if under limit */}
            {canAdd && (
              <button
                onClick={() => setShowAddModal(true)}
                className="w-full bg-white border-2 border-dashed border-[#1A5632] text-[#1A5632] py-4 rounded-2xl font-semibold flex items-center justify-center gap-2 hover:bg-green-50 transition"
              >
                <FaPlus /> Add Trusted Contact
              </button>
            )}
            
            {/* Limit reached message (subtle, no upgrade banner) */}
            {!canAdd && contacts.length > 0 && (
              <div className="text-center py-3">
                <p className="text-xs text-gray-400">
                  You have reached your trusted contact limit
                </p>
              </div>
            )}
          </>
        )}
      </div>
      
      {/* Add Contact Modal */}
      {showAddModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-2xl p-6 max-w-md w-full">
            <div className="flex justify-between items-center mb-5">
              <h3 className="text-xl font-bold text-[#1A5632]">Add Trusted Contact</h3>
              <button onClick={() => setShowAddModal(false)} className="text-gray-400 text-2xl hover:text-gray-600">✕</button>
            </div>
            
            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                <input
                  type="text"
                  value={formData.name}
                  onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                  placeholder="e.g., Sarah Johnson"
                  className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-[#1A5632] focus:ring-1 focus:ring-[#1A5632]"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                <input
                  type="tel"
                  value={formData.contact_phone}
                  onChange={(e) => setFormData({ ...formData, contact_phone: e.target.value })}
                  placeholder="08012345678 or +2348012345678"
                  className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:border-[#1A5632] focus:ring-1 focus:ring-[#1A5632]"
                />
                <p className="text-xs text-gray-500 mt-1">Enter Nigerian phone number (e.g., 08012345678)</p>
              </div>
              
              {formError && (
                <p className="text-red-500 text-sm text-center">{formError}</p>
              )}
              
              <div className="bg-blue-50 rounded-xl p-3">
                <p className="text-xs text-blue-800">
                  💡 This person will receive safety alerts if you miss a check-in or trigger SOS.
                </p>
              </div>
              
              <div className="flex gap-3 pt-3">
                <button
                  onClick={() => {
                    setShowAddModal(false);
                    setFormError("");
                    setFormData({ name: "", contact_phone: "" });
                  }}
                  className="flex-1 py-3 rounded-xl border border-gray-200 text-gray-700 font-medium hover:bg-gray-50 transition"
                >
                  Cancel
                </button>
                <button
                  onClick={addContact}
                  disabled={submitting}
                  className="flex-1 py-3 rounded-xl bg-[#1A5632] text-white font-medium hover:bg-[#2F6A44] transition disabled:opacity-50"
                >
                  {submitting ? "Adding..." : "Add Contact"}
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
      
      {/* Remove Confirmation Modal */}
      {showRemoveConfirm && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-2xl p-6 max-w-sm w-full">
            <div className="text-center mb-5">
              <div className="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <FaExclamationTriangle className="text-red-500 text-2xl" />
              </div>
              <h3 className="text-xl font-bold text-gray-900">Remove Contact?</h3>
              <p className="text-sm text-gray-500 mt-2">
                {showRemoveConfirm.name} will no longer receive safety alerts from you.
              </p>
              <p className="text-xs text-gray-400 mt-2">
                You can re-add them at any time.
              </p>
            </div>
            <div className="flex gap-3">
              <button
                onClick={() => setShowRemoveConfirm(null)}
                className="flex-1 py-3 rounded-xl border border-gray-200 text-gray-700 font-medium hover:bg-gray-50 transition"
              >
                Cancel
              </button>
              <button
                onClick={() => removeContact(showRemoveConfirm.id)}
                className="flex-1 py-3 rounded-xl bg-red-600 text-white font-medium hover:bg-red-700 transition"
              >
                Remove
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default NetworkScreen;
