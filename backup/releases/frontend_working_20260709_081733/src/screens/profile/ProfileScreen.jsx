// Profile Screen - Premium design matching CheckIn Settings
// User profile and settings with glass morphism design

import { useState, useEffect } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import BottomNav from "../../components/dashboard/BottomNav";
import { 
  FaArrowLeft, 
  FaShieldAlt, 
  FaStar, 
  FaClock,
  FaKey, 
  FaMapMarkerAlt, 
  FaQuestionCircle,
  FaSignOutAlt,
  FaChevronRight,
  FaToggleOn,
  FaToggleOff,
  FaUserCircle,
  FaPhone,
  FaEnvelope,
  FaUsers,
  FaLock
} from "react-icons/fa";

function ProfileScreen() {
  const location = useLocation();
  const navigate = useNavigate();
  const phone = location.state?.phone || localStorage.getItem("kin_phone");
  const API_BASE = import.meta.env.VITE_API_URL || "http://localhost:8000/api/v1";
  
  const [loading, setLoading] = useState(true);
  const [user, setUser] = useState(null);
  const [safetyScore, setSafetyScore] = useState(0);
  const [biometricEnabled, setBiometricEnabled] = useState(false);
  const [hasDuressPin, setHasDuressPin] = useState(false);
  
  useEffect(() => {
    if (!phone) {
      navigate("/login");
      return;
    }
    fetchUserData();
  }, [phone]);
  
  const fetchUserData = async () => {
    try {
      const response = await fetch(`${API_BASE}/dashboard?phone=${encodeURIComponent(phone)}`, {
        headers: {
          "Authorization": `Bearer ${localStorage.getItem("kin_token")}`,
          "Accept": "application/json",
        },
      });
      const data = await response.json();
      if (data.success) {
        setUser(data.data.user);
        setSafetyScore(95);
        const hasDuressTask = data.data.pending_tasks?.some(t => t.id === 'duress_pin');
        setHasDuressPin(!hasDuressTask);
      }
    } catch (error) {
      console.error("Error fetching user data:", error);
    } finally {
      setLoading(false);
    }
  };
  
  const handleSignOut = () => {
    localStorage.removeItem('kin_phone');
    localStorage.removeItem('kin_token');
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
  
  const userName = user?.name?.split(" ")[0] || "User";
  const userInitial = userName.charAt(0);
  
  return (
    <div className="min-h-screen bg-[#F0F7F2] pb-24">
      
      {/* Header */}
      <div className="bg-white px-5 py-4 border-b border-gray-100 sticky top-0 z-10">
        <div className="flex items-center gap-4">
          <button onClick={() => navigate(-1)} className="cursor-pointer">
            <FaArrowLeft className="text-[#1A5632] text-xl" />
          </button>
          <h1 className="text-xl font-bold text-[#1A5632]">Profile</h1>
        </div>
      </div>
      
      <div className="px-4 py-5 space-y-4 max-w-md mx-auto">
        
        {/* Profile Card - Premium Glass Design */}
        <div className="bg-white rounded-2xl p-6 shadow-sm text-center">
          <div className="relative inline-block">
            <div className="w-24 h-24 rounded-full bg-gradient-to-br from-[#1A5632] to-[#2F6A44] flex items-center justify-center text-white text-3xl font-bold shadow-md">
              {userInitial}
            </div>
            <div className="absolute bottom-0 right-0 w-6 h-6 bg-[#D4A017] rounded-full border-2 border-white flex items-center justify-center">
              <FaStar className="text-white text-[10px]" />
            </div>
          </div>
          <h2 className="text-2xl font-bold text-[#1A5632] mt-4">{user?.name || "User"}</h2>
          <div className="flex items-center justify-center gap-2 mt-2">
            <div className="w-2 h-2 rounded-full bg-green-500" />
            <span className="text-xs text-gray-500">Active</span>
          </div>
          <div className="mt-4 inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-50">
            <FaShieldAlt className="text-[#1A5632] text-sm" />
            <span className="text-sm font-medium text-[#1A5632]">Safety Score: {safetyScore}%</span>
          </div>
        </div>
        
        {/* Contact Info */}
        <div className="bg-white rounded-2xl p-4 shadow-sm">
          <div className="flex items-center gap-3 py-2">
            <FaPhone className="text-[#1A5632] text-sm" />
            <span className="text-sm text-gray-600">{user?.phone || phone}</span>
          </div>
          <div className="flex items-center gap-3 py-2 border-t border-gray-100">
            <FaEnvelope className="text-[#1A5632] text-sm" />
            <span className="text-sm text-gray-600">{user?.email || "user@kin.com"}</span>
          </div>
        </div>
        
        {/* Safety Settings Card */}
        <div className="bg-white rounded-2xl overflow-hidden shadow-sm">
          <div className="px-5 py-3 border-b border-gray-100">
            <h3 className="font-semibold text-[#1A5632]">Safety Settings</h3>
          </div>
          <div>
            <button 
              onClick={() => navigate("/checkin-settings", { state: { phone } })}
              className="w-full flex items-center justify-between px-5 py-4 hover:bg-gray-50 transition border-b border-gray-100"
            >
              <div className="flex items-center gap-3">
                <div className="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                  <FaClock className="text-[#1A5632] text-sm" />
                </div>
                <span className="text-sm text-gray-700">Check-in Settings</span>
              </div>
              <FaChevronRight className="text-gray-400 text-sm" />
            </button>
            
            <button 
              onClick={() => navigate("/settings/duress-pin", { state: { phone } })}
              className="w-full flex items-center justify-between px-5 py-4 hover:bg-gray-50 transition border-b border-gray-100"
            >
              <div className="flex items-center gap-3">
                <div className="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                  <FaKey className="text-red-500 text-sm" />
                </div>
                <div className="text-left">
                  <span className="text-sm text-gray-700">Duress PIN</span>
                  {hasDuressPin && <span className="text-xs text-green-600 ml-2">✓ Configured</span>}
                </div>
              </div>
              <FaChevronRight className="text-gray-400 text-sm" />
            </button>
            
            <button
              onClick={() => navigate("/dashboard", { state: { phone } })}
              className="w-full flex items-center justify-between px-5 py-4 hover:bg-gray-50 transition border-b border-gray-100"
            >
              <div className="flex items-center gap-3">
                <div className="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                  <FaMapMarkerAlt className="text-[#1A5632] text-sm" />
                </div>
                <span className="text-sm text-gray-700">Safe Zones</span>
              </div>
              <FaChevronRight className="text-gray-400 text-sm" />
            </button>

            <button
              onClick={() => navigate("/network", { state: { phone } })}
              className="w-full flex items-center justify-between px-5 py-4 hover:bg-gray-50 transition"
            >
              <div className="flex items-center gap-3">
                <div className="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                  <FaUsers className="text-[#1A5632] text-sm" />
                </div>
                <span className="text-sm text-gray-700">Trusted Contact</span>
              </div>
              <FaChevronRight className="text-gray-400 text-sm" />
            </button>
          </div>
        </div>
        
        {/* Privacy & Security Card */}
        <div className="bg-white rounded-2xl overflow-hidden shadow-sm">
          <div className="px-5 py-3 border-b border-gray-100">
            <h3 className="font-semibold text-[#1A5632]">Privacy & Security</h3>
          </div>
          <div>
            <button
              onClick={() => navigate("/forgot-pin", { state: { phone } })}
              className="w-full flex items-center justify-between px-5 py-4 hover:bg-gray-50 transition border-b border-gray-100"
            >
              <span className="text-sm text-gray-700">Change / Reset PIN</span>
              <FaChevronRight className="text-gray-400 text-sm" />
            </button>
            
            <div className="w-full flex items-center justify-between px-5 py-4">
              <span className="text-sm text-gray-700">Biometric Login</span>
              <button
                onClick={() => setBiometricEnabled(!biometricEnabled)}
                className="focus:outline-none"
              >
                {biometricEnabled ? (
                  <FaToggleOn className="text-2xl text-[#1A5632]" />
                ) : (
                  <FaToggleOff className="text-2xl text-gray-300" />
                )}
              </button>
            </div>
          </div>
        </div>
        
        {/* Help & Support Card */}
        <div className="bg-white rounded-2xl overflow-hidden shadow-sm">
          <button className="w-full flex items-center justify-between px-5 py-4 hover:bg-gray-50 transition">
            <div className="flex items-center gap-3">
              <div className="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                <FaQuestionCircle className="text-gray-500 text-sm" />
              </div>
              <span className="text-sm text-gray-700">Help &amp; Support</span>
            </div>
            <FaChevronRight className="text-gray-400 text-sm" />
          </button>
        </div>
        
        {/* Sign Out Button */}
        <button 
          onClick={handleSignOut}
          className="w-full py-3 rounded-xl bg-red-50 text-red-600 font-semibold text-sm hover:bg-red-100 transition border border-red-100 mt-4"
        >
          Sign Out
        </button>
        
        {/* Version Info */}
        <p className="text-center text-xs text-gray-400 py-4">
          KIN v1.0.0 • Protecting what matters
        </p>
      </div>
    <BottomNav activeTab="profile" />
    </div>
  );
}

export default ProfileScreen;
