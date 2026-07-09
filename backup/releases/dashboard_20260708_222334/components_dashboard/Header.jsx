import { FaShieldAlt, FaBell } from "react-icons/fa";

function Header({ greeting, userName }) {
  return (
    <div className="bg-white px-5 pt-6 pb-4 border-b border-[#E9ECEF] sticky top-0 z-10">
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-2">
          <FaShieldAlt className="text-[#1A5632] text-xl" />
          <h1 className="text-lg font-black text-[#1A5632] tracking-[0.15em]">KIN</h1>
        </div>
        <FaBell className="text-[#1A5632] text-lg" />
      </div>
      
      <h2 className="text-sm font-medium text-[#6C757D] mt-3">
        Good {greeting} <span className="font-semibold text-[#1A5632]">{userName}</span> 👋
      </h2>
    </div>
  );
}

export default Header;
