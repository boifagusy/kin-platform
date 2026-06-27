import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { FaArrowLeft, FaShieldAlt, FaClock, FaChevronRight, FaPhone, FaEnvelope, FaUsers, FaSignOutAlt, FaKey } from "react-icons/fa";

const API_BASE = import.meta.env.VITE_API_URL;

function ProfileScreenV2() {
  const navigate = useNavigate();
  const phone = localStorage.getItem("kin_phone");

  const [loading, setLoading] = useState(true);
  const [user, setUser] = useState(null);
  const [safetyScore, setSafetyScore] = useState(0);
  const [scoreLabel, setScoreLabel] = useState("Good");
  const [hasDuressPin, setHasDuressPin] = useState(false);
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
      const token = localStorage.getItem("kin_token");
      const response = await fetch(`${API_BASE}/dashboard`, {
        headers: {
          "Authorization": `Bearer ${token}`,
          "Accept": "application/json"
        }
      });
      
      if (response.status === 401) {
        handleSignOut();
        return;
      }

      const resData = await response.json();
      alert("API RESPONSE: " + JSON.stringify(resData.data?.user));
      if (resData.success && resData.data) {
        const data = resData.data;
        setUser(data.user);
        setSafetyScore(data.safety_score || 0);
        setScoreLabel(data.score_label || "Good");
                const hasDuressTask = data.pending_tasks?.some(t => t.id === 'duress_pin');
        setHasDuressPin(!hasDuressTask);
        setContactsCount(data.user?.contacts_count || 0); 
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
      <div className="min-h-screen bg-gradient-to-br from-[#F4F9F5] to-[#E8F0EA] flex items-center justify-center">
        <div className="text-center">
          <div className="w-12 h-12 border-4 border-[#1A5632] border-t-transparent rounded-full animate-spin mx-auto mb-4" />
          <p className="text-[#1A5632] font-medium">Loading your profile...</p>
        </div>
      </div>
    );
  }

  const userName = user?.name || "User";
  const userInitial = userName.charAt(0).toUpperCase();
  const scoreColor = safetyScore >= 80 ? "text-green-600" : safetyScore >= 50 ? "text-yellow-600" : "text-red-500";
  const scoreBg = safetyScore >= 80 ? "bg-green-50 border-green-100" : safetyScore >= 50 ? "bg-yellow-50 border-yellow-100" : "bg-red-50 border-red-100";

  return (
    <div className="min-h-screen bg-gradient-to-br from-[#F4F9F5] to-[#E8F0EA] pb-24">
      {/* Header */}
      <div className="bg-white/80 backdrop-blur-md px-5 py-4 border-b border-[#E9ECEF]/50 sticky top-0 z-10">
        <div className="flex items-center justify-between max-w-md mx-auto">
          <button onClick={() => navigate(-1)} className="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center active:scale-95 transition-all">
            <FaArrowLeft className="text-[#1A5632]" />
          </button>
          <h1 className="text-lg font-bold text-[#1A1A1A]">Profile</h1>
          <div className="w-10" /> 
        </div>
      </div>

      <div className="px-4 py-6 space-y-5 max-w-md mx-auto">
        {/* Premium Profile Card */}
        <div className="relative bg-white rounded-3xl p-6 shadow-lg shadow-[#1A5632]/5 border border-white overflow-hidden">
          <div className="absolute top-0 right-0 w-32 h-32 bg-[#1A5632]/5 rounded-full blur-2xl -mr-10 -mt-10" />          <div className="absolute bottom-0 left-0 w-24 h-24 bg-[#D4A017]/5 rounded-full blur-2xl -ml-10 -mb-10" />
          
          <div className="relative flex flex-col items-center">
            <div className="relative">
              <div className="w-28 h-28 rounded-full bg-gradient-to-br from-[#1A5632] to-[#0E3A22] flex items-center justify-center text-white text-4xl font-bold shadow-lg ring-4 ring-white">
                {userInitial}
              </div>
              <div className="absolute -bottom-1 -right-1 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center ring-4 ring-white">
                <svg className="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" />
                </svg>
              </div>
            </div>
            
            <h2 className="text-2xl font-bold text-[#1A1A1A] mt-4 tracking-tight">{userName}</h2>
            <p className="text-sm text-[#6C757D] mt-1">{user?.phone || phone}</p>
            
            <div className={`mt-5 inline-flex items-center gap-2 px-4 py-2 rounded-full border ${scoreBg}`}>
              <FaShieldAlt className={`${scoreColor} text-sm`} />
              <span className={`text-sm font-bold ${scoreColor}`}>{safetyScore}%</span>
              <span className="text-xs text-[#6C757D] font-medium">• {scoreLabel}</span>
            </div>
          </div>
        </div>

        {/* Contact Info */}
        <div className="bg-white rounded-2xl p-2 shadow-sm border border-[#E9ECEF]/50">
          <div className="flex items-center gap-4 p-3 rounded-xl hover:bg-[#F4F9F5] transition">
            <div className="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center">
              <FaPhone className="text-blue-500 text-sm" />
            </div>
            <div>
              <p className="text-xs text-[#6C757D] font-medium">Phone Number</p>
              <p className="text-sm font-semibold text-[#1A1A1A]">{user?.phone || phone}</p>
            </div>
          </div>
          <div className="flex items-center gap-4 p-3 rounded-xl hover:bg-[#F4F9F5] transition">
            <div className="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center">
              <FaEnvelope className="text-purple-500 text-sm" />
            </div>
            <div>
              <p className="text-xs text-[#6C757D] font-medium">Email Address</p>
              <p className="text-sm font-semibold text-[#1A1A1A]">{user?.email || "Not provided"}</p>
            </div>
          </div>
        </div>

        {/* Settings Sections */}
        <div className="space-y-2">
          <h3 className="text-xs font-bold text-[#6C757D] uppercase tracking-wider px-2 mb-2">Security & Safety</h3>          <div className="bg-white rounded-2xl shadow-sm border border-[#E9ECEF]/50 overflow-hidden divide-y divide-[#E9ECEF]/50">
            
            <button onClick={() => navigate("/forgot-pin")} className="w-full flex items-center justify-between p-4 active:bg-[#F4F9F5] transition text-left">
              <div className="flex items-center gap-4">
                <div className="w-10 h-10 rounded-full bg-orange-50 flex items-center justify-center">
                  <FaKey className="text-orange-500 text-sm" />
                </div>
                <span className="text-sm font-medium text-[#1A1A1A]">Change PIN</span>
              </div>
              <FaChevronRight className="text-[#C8D1CB] text-sm" />
            </button>

            <button onClick={() => navigate("/settings/duress-pin", { state: { phone } })} className="w-full flex items-center justify-between p-4 active:bg-[#F4F9F5] transition text-left">
              <div className="flex items-center gap-4">
                <div className="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center">
                  <svg className="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                </div>
                <div>
                  <span className="text-sm font-medium text-[#1A1A1A]">Duress PIN</span>
                  <p className="text-xs text-[#6C757D]">{hasDuressPin ? "Configured" : "Not set"}</p>
                </div>
              </div>
              <FaChevronRight className="text-[#C8D1CB] text-sm" />
            </button>

            <button onClick={() => navigate("/checkin-settings", { state: { phone } })} className="w-full flex items-center justify-between p-4 active:bg-[#F4F9F5] transition text-left">
              <div className="flex items-center gap-4">
                <div className="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center">
                  <FaClock className="text-[#1A5632] text-sm" />
                </div>
                <span className="text-sm font-medium text-[#1A1A1A]">Check-in Settings</span>
              </div>
              <FaChevronRight className="text-[#C8D1CB] text-sm" />
            </button>

            <button onClick={() => navigate("/network", { state: { phone } })} className="w-full flex items-center justify-between p-4 active:bg-[#F4F9F5] transition text-left">
              <div className="flex items-center gap-4">
                <div className="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center">
                  <FaUsers className="text-indigo-500 text-sm" />
                </div>
                <div>
                  <span className="text-sm font-medium text-[#1A1A1A]">Trusted Contacts</span>
                  <p className="text-xs text-[#6C757D]">{contactsCount > 0 ? `${contactsCount} added` : "Manage network"}</p>
                </div>
              </div>
              <FaChevronRight className="text-[#C8D1CB] text-sm" />
            </button>
          </div>
        </div>
        {/* Sign Out */}
        <button
          onClick={handleSignOut}
          className="w-full py-4 rounded-2xl bg-white text-red-500 font-semibold text-sm shadow-sm border border-red-100 active:bg-red-50 transition flex items-center justify-center gap-2"
        >
          <FaSignOutAlt />
          Sign Out
        </button>

        <p className="text-center text-xs text-[#A1AAB3] pt-2">
          KIN v1.0.0 • Protecting what matters
        </p>
      </div>
    </div>
  );
}

export default ProfileScreenV2;
