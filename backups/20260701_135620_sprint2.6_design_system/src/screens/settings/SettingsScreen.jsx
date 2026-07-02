import { useLocation, useNavigate } from "react-router-dom";
import {
  FaArrowLeft,
  FaClock,
  FaKey,
  FaMapMarkerAlt,
  FaUsers,
  FaLock,
  FaQuestionCircle,
  FaChevronRight,
  FaSignOutAlt,
} from "react-icons/fa";

function SettingsScreen() {
  const location = useLocation();
  const navigate = useNavigate();
  const phone = location.state?.phone || localStorage.getItem("kin_phone");

  const handleSignOut = () => {
    localStorage.removeItem("kin_phone");
    localStorage.removeItem("kin_token");
    navigate("/login");
  };

  const Section = ({ title, children }) => (
    <div className="bg-white rounded-2xl overflow-hidden shadow-sm mb-4">
      <div className="px-5 py-3 border-b border-gray-100">
        <h3 className="font-semibold text-[#1A5632]">{title}</h3>
      </div>
      <div>{children}</div>
    </div>
  );

  const Item = ({ icon, iconBg, iconColor, label, onClick, badge }) => (
    <button
      onClick={onClick}
      className="w-full flex items-center justify-between px-5 py-4 hover:bg-gray-50 transition border-b border-gray-100 last:border-b-0"
    >
      <div className="flex items-center gap-3">
        <div className={`w-8 h-8 rounded-full ${iconBg} flex items-center justify-center`}>
          {icon}
        </div>
        <div className="text-left">
          <span className="text-sm text-gray-700">{label}</span>
          {badge && <span className="text-xs text-green-600 ml-2">{badge}</span>}
        </div>
      </div>
      <FaChevronRight className="text-gray-400 text-sm" />
    </button>
  );

  return (
    <div className="min-h-screen bg-[#F0F7F2] pb-24">
      <div className="bg-white px-5 py-4 border-b border-gray-100 sticky top-0 z-10">
        <div className="flex items-center gap-4">
          <button onClick={() => navigate(-1)} className="cursor-pointer">
            <FaArrowLeft className="text-[#1A5632] text-xl" />
          </button>
          <h1 className="text-xl font-bold text-[#1A5632]">Settings</h1>
        </div>
      </div>

      <div className="px-4 py-5 space-y-4 max-w-md mx-auto">
        <Section title="Safety Settings">
          <Item
            icon={<FaClock className="text-[#1A5632] text-sm" />}
            iconBg="bg-green-100"
            label="Check-in Settings"
            onClick={() => navigate("/checkin-settings", { state: { phone } })}
          />
          <Item
            icon={<FaKey className="text-red-500 text-sm" />}
            iconBg="bg-red-100"
            label="Duress PIN"
            onClick={() => navigate("/settings/duress-pin", { state: { phone } })}
          />
          <Item
            icon={<FaMapMarkerAlt className="text-[#1A5632] text-sm" />}
            iconBg="bg-green-100"
            label="Safe Zones"
            onClick={() => navigate("/dashboard", { state: { phone } })}
          />
          <Item
            icon={<FaUsers className="text-[#1A5632] text-sm" />}
            iconBg="bg-green-100"
            label="Trusted Contact"
            onClick={() => navigate("/network", { state: { phone } })}
          />
        </Section>

        <Section title="Account">
          <Item
            icon={<FaLock className="text-gray-500 text-sm" />}
            iconBg="bg-gray-100"
            label="Forgot / Reset PIN"
            onClick={() => navigate("/forgot-pin", { state: { phone } })}
          />
        </Section>

        <Section title="Support">
          <Item
            icon={<FaQuestionCircle className="text-gray-500 text-sm" />}
            iconBg="bg-gray-100"
            label="Help & Support"
            onClick={() => {}}
          />
        </Section>

        <button
          onClick={handleSignOut}
          className="w-full py-3 rounded-xl bg-red-50 text-red-600 font-semibold text-sm hover:bg-red-100 transition border border-red-100 flex items-center justify-center gap-2"
        >
          <FaSignOutAlt /> Sign Out
        </button>

        <p className="text-center text-xs text-gray-400 py-4">
          KIN v1.0.0 • Protecting what matters
        </p>
      </div>
    </div>
  );
}

export default SettingsScreen;
