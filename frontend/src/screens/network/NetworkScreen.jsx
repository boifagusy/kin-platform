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
import BottomNav from "../../components/dashboard/BottomNav";

const API_BASE = import.meta.env.VITE_API_URL;

function NetworkScreen() {
  const location = useLocation();
  const navigate = useNavigate();
  const phone = location.state?.phone || localStorage.getItem("kin_phone");

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
  const [activeTab, setActiveTab] = useState("network");

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

  const validatePhoneNumber = (phoneNumber) => {
    const cleaned = phoneNumber.replace(/\D/g, '');
    const isValid = cleaned.length === 10 || cleaned.length === 13;
    if (!isValid) {
      return "Invalid phone number. Use format: 08012345678 or +2348012345678";
    }
    return null;
  };

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
      const response = await fetch(`${API_BASE}/trusted-contacts`, {
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
