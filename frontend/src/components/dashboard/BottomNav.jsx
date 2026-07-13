import { useNavigate } from "react-router-dom";
function BottomNav({ onSOS, hasTrustedContact = true, hasActiveSOS = false, activeTab = "home", onTabChange }) {
  const navigate = useNavigate();
  const handleTabClick = (tab) => {
    if (onTabChange) onTabChange(tab);
    switch (tab) { case "home": navigate("/dashboard"); break; case "network": navigate("/network"); break; case "alerts": navigate("/alerts"); break; case "profile": navigate("/profile"); break; }
  };
  const isActive = (tab) => activeTab === tab;
  return (
    <nav className="fixed bottom-0 left-0 right-0 bg-white border-t border-[#E9ECEF] px-4 py-2 flex items-center justify-around max-w-md mx-auto z-50">
      <button onClick={() => handleTabClick("home")} className={`flex flex-col items-center gap-0.5 ${isActive("home") ? "text-[#1A5632]" : "text-[#6C757D]"}`}><span className="material-symbols-outlined text-xl">home</span><span className="text-[10px] font-medium">Home</span></button>
      <button onClick={() => handleTabClick("network")} className={`flex flex-col items-center gap-0.5 ${isActive("network") ? "text-[#1A5632]" : "text-[#6C757D]"}`}><span className="material-symbols-outlined text-xl">group</span><span className="text-[10px] font-medium">Network</span></button>
      <button onClick={onSOS} disabled={!hasTrustedContact || hasActiveSOS} className={`relative -mt-6 w-14 h-14 rounded-full shadow-lg flex items-center justify-center transition-all ${hasTrustedContact && !hasActiveSOS ? "bg-gradient-to-br from-[#DC3545] to-[#b02a37] shadow-red-500/30 active:scale-90" : "bg-gray-300 cursor-not-allowed"}`}><span className={`text-2xl font-bold ${hasTrustedContact && !hasActiveSOS ? "text-white" : "text-gray-500"}`}>SOS</span></button>
      <button onClick={() => handleTabClick("alerts")} className={`flex flex-col items-center gap-0.5 ${isActive("alerts") ? "text-[#1A5632]" : "text-[#6C757D]"}`}><span className="material-symbols-outlined text-xl">notifications</span><span className="text-[10px] font-medium">Alerts</span></button>
      <button onClick={() => handleTabClick("profile")} className={`flex flex-col items-center gap-0.5 ${isActive("profile") ? "text-[#1A5632]" : "text-[#6C757D]"}`}><span className="material-symbols-outlined text-xl">person</span><span className="text-[10px] font-medium">Profile</span></button>
    </nav>
  );
}
export default BottomNav;
