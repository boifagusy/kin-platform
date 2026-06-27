import { FaBell } from "react-icons/fa";

function HeaderV2({ greeting, userName }) {
  // Extract just the first name (e.g., "John Doe" -> "John")
  const firstName = userName ? userName.split(' ')[0] : '';

  return (
    <div className="bg-white px-5 pt-6 pb-4 border-b border-[#E9ECEF]">
      {/* Top row: Shield + KIN + Bell */}
      <div className="flex items-center justify-between mb-2">
        <div className="flex items-center gap-2">
          <span className="material-symbols-outlined text-[#1A5632] text-2xl">shield</span>
          <span className="text-lg font-black text-[#1A5632] tracking-[0.15em]">KIN</span>
        </div>
        <button className="w-9 h-9 rounded-full flex items-center justify-center hover:bg-[#F0F7F2] transition-colors">
          <FaBell className="text-[#1A5632] text-lg" />
        </button>
      </div>

      {/* Greeting + User — single line */}
      <p className="text-sm text-[#6C757D]">
        Good {greeting} <span className="font-semibold text-[#1A5632]">{firstName}</span> 👋
      </p>
    </div>
  );
}

export default HeaderV2;
