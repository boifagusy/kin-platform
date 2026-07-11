import { FaBell } from "react-icons/fa";

function ReminderCard({ title, buttonText }) {
  return (
    <div className="bg-[#1A5632] rounded-2xl p-5 text-white">
      <div className="flex items-center gap-2 mb-3">
        <FaBell className="text-yellow-400" size={16} />
        <p className="text-sm opacity-80">{title}</p>
      </div>
      <p className="text-xl font-bold mt-2">Daily Check-in</p>
      <button className="mt-4 bg-white text-[#1A5632] py-3 rounded-xl font-semibold w-full hover:bg-gray-100 transition">
        {buttonText}
      </button>
    </div>
  );
}

export default ReminderCard;
