import { FaHome, FaUsers, FaBell, FaUser } from "react-icons/fa";
import { useNavigate } from "react-router-dom";

function BottomNav({ onSOS, activeTab = "home", onTabChange }) {
  const navigate = useNavigate();

  const handleTabClick = (tab) => {
    if (onTabChange) {
      onTabChange(tab);
    }
    
    switch (tab) {
      case "home":
        navigate("/dashboard");
        break;
      case "network":
        navigate("/network");
        break;
      case "alerts":
        navigate("/alerts");
        break;
      case "profile":
        navigate("/profile");
        break;
      default:
        break;
    }
  };

  const isActive = (tab) => activeTab === tab;

  return (
    <nav className="fixed bottom-0 left-0 right-0 bg-white border-t border-[#E9ECEF] px-4 py-2 flex items-center justify-around max-w-md mx-auto z-50">
      {/* Home */}
      <button
        onClick={() => handleTabClick("home")}
        className={`flex flex-col items-center gap-0.5 ${
          isActive("home") ? "text-[#1A5632]" : "text-[#6C757D]"
        }`}
      >
        <FaHome className="text-xl" />
        <span className="text-[10px] font-medium">Home</span>
      </button>

      {/* Network */}
      <button
        onClick={() => handleTabClick("network")}
        className={`flex flex-col items-center gap-0.5 ${
          isActive("network") ? "text-[#1A5632]" : "text-[#6C757D]"
        }`}
      >
        <FaUsers className="text-xl" />
        <span className="text-[10px] font-medium">Network</span>
      </button>

      {/* SOS — Center, prominent */}
      <button
        onClick={onSOS}
        className="relative -mt-6 w-14 h-14 rounded-full bg-gradient-to-br from-[#DC3545] to-[#b02a37] shadow-lg shadow-red-500/30 flex items-center justify-center active:scale-90 transition-transform"
      >
        <span className="text-2xl font-bold text-white">SOS</span>
      </button>

      {/* Alerts */}
      <button
        onClick={() => handleTabClick("alerts")}
        className={`flex flex-col items-center gap-0.5 ${
          isActive("alerts") ? "text-[#1A5632]" : "text-[#6C757D]"
        }`}
      >
        <FaBell className="text-xl" />
        <span className="text-[10px] font-medium">Alerts</span>
      </button>

      {/* Profile */}
      <button
        onClick={() => handleTabClick("profile")}
        className={`flex flex-col items-center gap-0.5 ${
          isActive("profile") ? "text-[#1A5632]" : "text-[#6C757D]"
        }`}
      >
        <FaUser className="text-xl" />
        <span className="text-[10px] font-medium">Profile</span>
      </button>
    </nav>
  );
}

export default BottomNav;
