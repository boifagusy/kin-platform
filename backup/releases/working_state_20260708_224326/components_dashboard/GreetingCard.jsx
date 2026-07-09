import { FaHandPeace } from "react-icons/fa";

function GreetingCard({ userName }) {
  const hour = new Date().getHours();
  const greeting = hour < 12 ? "Morning" : hour < 18 ? "Afternoon" : "Evening";

  return (
    <div className="bg-white rounded-2xl p-5 shadow-sm">
      <div className="flex justify-between items-start">
        <div>
          <p className="text-sm text-gray-500">Good {greeting},</p>
          <h3 className="text-2xl font-bold text-[#1A5632] mt-1">{userName}</h3>
          <div className="flex items-center gap-2 mt-3">
            <span className="w-2 h-2 rounded-full bg-green-500" />
            <span className="text-xs text-gray-500">Connected • SMS Available</span>
          </div>
        </div>
        <div className="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
          <FaHandPeace className="text-yellow-700" size={18} />
        </div>
      </div>
    </div>
  );
}

export default GreetingCard;
