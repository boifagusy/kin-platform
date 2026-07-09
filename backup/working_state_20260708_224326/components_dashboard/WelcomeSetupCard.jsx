import { useNavigate } from "react-router-dom";

function WelcomeSetupCard({ hasContact }) {
  const navigate = useNavigate();

  if (hasContact) {
    return null; // Don't show if contact exists
  }

  return (
    <div className="bg-gradient-to-r from-[#1A5632] to-[#0E3A22] rounded-2xl p-5 text-white shadow-lg">
      <h3 className="text-lg font-bold mb-2">Welcome to KIN</h3>
      <p className="text-sm text-white/80 mb-4">
        Add your emergency contact to activate full protection.
      </p>
      <button
        onClick={() => navigate("/network")}
        className="w-full py-3 rounded-xl bg-white text-[#1A5632] font-semibold text-sm active:scale-95 transition-all"
      >
        Add Contact
      </button>
    </div>
  );
}

export default WelcomeSetupCard;
