import { useState, useEffect } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import {
  FaArrowLeft,
  FaShieldAlt,
  FaClock,
  FaKey,
  FaMapMarkerAlt,
  FaChevronRight,
  FaPhone,
  FaEnvelope,
  FaUsers
} from "react-icons/fa";

const API_BASE = import.meta.env.VITE_API_URL || "http://localhost:8000";

function ProfileScreenV2() {
  const location = useLocation();
  const navigate = useNavigate();
  const phone = location.state?.phone || localStorage.getItem("kin_phone");

  const [loading, setLoading] = useState(true);
  const [user, setUser] = useState(null);
  const [safetyScore, setSafetyScore] = useState(0);
  const [hasDuressPin, setHasDuressPin] = useState(false);
  const [hasTrustedContact, setHasTrustedContact] = useState(false);
  const [contactsCount, setContactsCount] = useState(0);

  useEffect(() => {
    if (!phone) {
      navigate("/login");
      return;
    }
    fetchUserData();
  }, [phone]);

  const fetchUserData = async () => {
    try {
      const response = await fetch(`${API_BASE}/api/v1/dashboard?phone=${encodeURIComponent(phone)}`);
      const data = await response.json();
      if (data.success) {
        setUser(data.user);
        setSafetyScore(data.safety_score || 0);
        const hasDuressTask = data.pending_tasks?.some(t => t.id === 'duress_pin');
        setHasDuressPin(!hasDuressTask);
        setContactsCount(data.data?.contacts_count || 0);
        setHasTrustedContact(data.data?.has_verified_contact || false);
      }
    } catch (error) {
      console.error("Error fetching user data:", error);
    } finally {
      setLoading(false);
    }
  };

  const handleSignOut = () => {
    localStorage.removeItem('kin_phone');
    navigate("/login");
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-[#F0F7F2] flex items-center justify-center">
        <div className="text-center">
          <div className="w-10 h-10 border-3 border-[#1A5632] border-t-transparent rounded-full animate-spin mx-auto mb-3" />
          <p className="text-[#1A5632] text-sm">Loading profile...</p>
        </div>
      </div>
    );
  }

  const userName = user?.name || "User";
  const userInitial = userName.charAt(0).toUpperCase();

  return (
    <div className="min-h-screen bg-[#F0F7F2] pb-24">
      {/* Header */}
      <div className="bg-white px-5 py-4 border-b border-[#E9ECEF] sticky top-0 z-10">
        <div className="flex items-center gap-4">
          <button onClick={() => navigate(-1)} className="text-[#1A5632] text-xl">
            <FaArrowLeft />
          </button>
          <h1 className="text-lg font-bold text-[#1A5632]">Profile</h1>
        </div>
      </div>

      <div className="px-4 py-5 space-y-4 max-w-md mx-auto">
        {/* Profile Card */}
        <div className="bg-white rounded-2xl p-6 shadow-sm border border-[#E9ECEF] text-center">
          <div className="relative inline-block">
            <div className="w-24 h-24 rounded-full bg-gradient-to-br from-[#1A5632] to-[#0E3A22] flex items-center justify-center text-white text-3xl font-bold shadow-md">
              {userInitial}
            </div>
          </div>
          <h2 className="text-xl font-bold text-[#1A1A1A] mt-4">{userName}</h2>
          <div className="flex items-center justify-center gap-2 mt-1">
            <div className="w-2 h-2 rounded-full bg-green-500" />
            <span className="text-xs text-[#6C757D]">Active</span>
          </div>
          <div className="mt-4 inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#E8F3EA]">
            <FaShieldAlt className="text-[#1A5632] text-sm" />
            <span className="text-sm font-medium text-[#1A5632]">Safety Score: {safetyScore}%</span>
          </div>
        </div>

        {/* Contact Info */}
        <div className="bg-white rounded-2xl p-4 shadow-sm border border-[#E9ECEF]">
          <div className="flex items-center gap-3 py-2">
            <FaPhone className="text-[#1A5632] text-sm" />
            <span className="text-sm text-[#1A1A1A]">{user?.phone || phone}</span>
          </div>
          <div className="flex items-center gap-3 py-2 border-t border-[#E9ECEF]">
            <FaEnvelope className="text-[#1A5632] text-sm" />
            <span className="text-sm text-[#1A1A1A]">{user?.email || "No email set"}</span>
          </div>
        </div>

        {/* Security Section */}
        <div className="bg-white rounded-2xl overflow-hidden shadow-sm border border-[#E9ECEF]">
          <div className="px-5 py-3 border-b border-[#E9ECEF]">
            <h3 className="font-semibold text-[#1A5632]">Security</h3>
          </div>
          <div>
            <button
              onClick={() => navigate("/forgot-pin")}
              className="w-full flex items-center justify-between px-5 py-4 hover:bg-[#F0F7F2] transition border-b border-[#E9ECEF]"
            >
              <div className="flex items-center gap-3">
                <div className="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                  <svg className="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                </div>
                <span className="text-sm text-[#1A1A1A]">Change PIN</span>
              </div>
              <FaChevronRight className="text-[#6C757D] text-sm" />
            </button>
            <button
              onClick={() => navigate("/settings/duress-pin", { state: { phone } })}
              className="w-full flex items-center justify-between px-5 py-4 hover:bg-[#F0F7F2] transition"
            >
              <div className="flex items-center gap-3">
                <div className="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                  <svg className="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                </div>
                <div className="text-left">
                  <span className="text-sm text-[#1A1A1A]">Duress PIN</span>
                  {hasDuressPin && <span className="text-xs text-green-600 ml-2">✓ Configured</span>}
                </div>
              </div>
              <FaChevronRight className="text-[#6C757D] text-sm" />
            </button>
          </div>
        </div>

        {/* Safety Settings */}
        <div className="bg-white rounded-2xl overflow-hidden shadow-sm border border-[#E9ECEF]">
          <div className="px-5 py-3 border-b border-[#E9ECEF]">
            <h3 className="font-semibold text-[#1A5632]">Safety Settings</h3>
          </div>
          <div>
            <button
              onClick={() => navigate("/checkin-settings", { state: { phone } })}
              className="w-full flex items-center justify-between px-5 py-4 hover:bg-[#F0F7F2] transition border-b border-[#E9ECEF]"
            >
              <div className="flex items-center gap-3">
                <div className="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                  <FaClock className="text-[#1A5632] text-sm" />
                </div>
                <span className="text-sm text-[#1A1A1A]">Check-in Settings</span>
              </div>
              <FaChevronRight className="text-[#6C757D] text-sm" />
            </button>
            <button onClick={() => navigate("/settings/safe-zones")}
              className="w-full flex items-center justify-between px-5 py-4 hover:bg-[#F0F7F2] transition"
            >
              <div className="flex items-center gap-3">
                <div className="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                  <FaMapMarkerAlt className="text-blue-500 text-sm" />
                </div>
                <span className="text-sm text-[#1A1A1A]">Safe Zones</span>
              </div>
            </button>
            </button>
        <div className="bg-white rounded-2xl overflow-hidden shadow-sm border border-[#E9ECEF]">
          <div className="px-5 py-3 border-b border-[#E9ECEF]">
            <h3 className="font-semibold text-[#1A5632]">Trusted Contact</h3>
          </div>
          <button
            onClick={() => navigate("/network", { state: { phone } })}
            className="w-full flex items-center justify-between px-5 py-4 hover:bg-[#F0F7F2] transition"
          >
            <div className="flex items-center gap-3">
              <div className="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center">
                <FaUsers className="text-purple-500 text-sm" />
              </div>
              <div className="text-left">
                <span className="text-sm text-[#1A1A1A]">Trusted Contact</span>
                <p className="text-xs text-[#6C757D]">
                  {hasTrustedContact && contactsCount === 1 ? "1 trusted contact configured" : "No trusted contact added"}
                </p>
              </div>
            </div>
            <FaChevronRight className="text-[#6C757D] text-sm" />
          </button>
        </div>

        {/* Sign Out Button */}
        <button
          onClick={handleSignOut}
          className="w-full py-3 rounded-xl bg-red-50 text-red-600 font-semibold text-sm hover:bg-red-100 transition border border-red-100"
        >
          Sign Out
        </button>

        {/* Version Info */}
        <p className="text-center text-xs text-[#6C757D] py-4">
          KIN v1.0.0 • Protecting what matters
        </p>
      </div>
    </div>
  );
}

export default ProfileScreenV2;
