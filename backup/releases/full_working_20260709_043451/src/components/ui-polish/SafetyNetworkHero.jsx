import { useEffect, useState } from "react";

function SafetyNetworkHero() {
  const [isVisible, setIsVisible] = useState(false);

  useEffect(() => {
    setIsVisible(true);
  }, []);

  return (
    <div
      className={`relative w-48 h-48 mx-auto transition-all duration-700 ease-out ${
        isVisible ? "opacity-100 scale-100" : "opacity-0 scale-90"
      }`}
    >
      {/* Outer pulsing ring */}
      <div className="absolute inset-0 rounded-full border-4 border-primary/20 animate-ping" />

      {/* Middle ring */}
      <div className="absolute inset-2 rounded-full border-4 border-primary/40" />

      {/* Center — User */}
      <div className="absolute inset-4 rounded-full bg-primary shadow-xl flex items-center justify-center">
        <span
          className="material-symbols-outlined text-white text-4xl"
          style={{ fontVariationSettings: "'FILL' 1" }}
        >
          person
        </span>
      </div>

      {/* Orbiting nodes — Mom (top) */}
      <div
        className="absolute -top-3 left-1/2 -translate-x-1/2 w-12 h-12 rounded-full bg-secondary shadow-lg flex items-center justify-center animate-bounce"
        style={{ animationDelay: "0s", animationDuration: "2s" }}
      >
        <span className="material-symbols-outlined text-white text-xl">favorite</span>
      </div>
      <span className="absolute -top-7 left-1/2 -translate-x-1/2 text-xs font-medium text-primary whitespace-nowrap">
        Mom
      </span>

      {/* Orbiting nodes — Friend (left) */}
      <div
        className="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-6 w-10 h-10 rounded-full bg-primary-light shadow-lg flex items-center justify-center animate-bounce"
        style={{ animationDelay: "0.3s", animationDuration: "2.2s" }}
      >
        <span className="material-symbols-outlined text-white text-lg">people</span>
      </div>
      <span className="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-14 text-xs font-medium text-primary whitespace-nowrap">
        Friend
      </span>

      {/* Orbiting nodes — Partner (right) */}
      <div
        className="absolute right-0 top-1/2 -translate-y-1/2 translate-x-6 w-10 h-10 rounded-full bg-primary-light shadow-lg flex items-center justify-center animate-bounce"
        style={{ animationDelay: "0.6s", animationDuration: "2.4s" }}
      >
        <span className="material-symbols-outlined text-white text-lg">favorite</span>
      </div>
      <span className="absolute right-0 top-1/2 -translate-y-1/2 translate-x-14 text-xs font-medium text-primary whitespace-nowrap">
        Partner
      </span>

      {/* Orbiting nodes — Trusted Contact (bottom) */}
      <div
        className="absolute -bottom-3 left-1/2 -translate-x-1/2 w-12 h-12 rounded-full bg-secondary-light shadow-lg flex items-center justify-center animate-bounce"
        style={{ animationDelay: "0.9s", animationDuration: "2.6s" }}
      >
        <span className="material-symbols-outlined text-white text-xl">shield</span>
      </div>
      <span className="absolute -bottom-8 left-1/2 -translate-x-1/2 text-xs font-medium text-primary whitespace-nowrap">
        Trusted Contact
      </span>

      {/* Connecting lines (SVG) */}
      <svg className="absolute inset-0 w-full h-full pointer-events-none">
        <line
          x1="50%"
          y1="50%"
          x2="50%"
          y2="0%"
          stroke="#1A5632"
          strokeWidth="2"
          strokeDasharray="4"
          className="opacity-40"
        />
        <line
          x1="50%"
          y1="50%"
          x2="0%"
          y2="50%"
          stroke="#1A5632"
          strokeWidth="2"
          strokeDasharray="4"
          className="opacity-40"
        />
        <line
          x1="50%"
          y1="50%"
          x2="100%"
          y2="50%"
          stroke="#1A5632"
          strokeWidth="2"
          strokeDasharray="4"
          className="opacity-40"
        />
        <line
          x1="50%"
          y1="50%"
          x2="50%"
          y2="100%"
          stroke="#1A5632"
          strokeWidth="2"
          strokeDasharray="4"
          className="opacity-40"
        />
      </svg>
    </div>
  );
}

export default SafetyNetworkHero;
