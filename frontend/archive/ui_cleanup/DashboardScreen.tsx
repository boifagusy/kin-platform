import React from "react";
import {
  Bell,
  Users,
  MapPin,
  Gift,
  AlertCircle,
  ChevronRight,
  ShieldAlert,
} from "lucide-react";

export default function DashboardScreen() {
  return (
    <div className="min-h-screen bg-[#F8FBF9] flex flex-col">

      {/* HEADER */}
      <header className="sticky top-0 z-50 h-14 bg-white border-b border-gray-100 flex items-center justify-center">
        <h1 className="font-bold text-xl text-[#1A5632]">
          KIN
        </h1>
      </header>

      {/* CONTENT */}
      <main className="flex-1 overflow-y-auto px-4 py-4 space-y-4 pb-28">

        {/* GREETING */}
        <section className="bg-white rounded-3xl p-5 shadow-sm">
          <h2 className="text-2xl font-bold text-[#1A5632]">
            Good Evening Idowu
          </h2>

          <div className="flex items-center gap-2 mt-3">
            <span className="w-2 h-2 rounded-full bg-green-500"></span>
            <span className="text-sm text-gray-500">
              Connected • SMS Available
            </span>
          </div>
        </section>

        {/* METRICS */}
        <div className="grid grid-cols-2 gap-4">

          <div className="bg-white rounded-3xl p-5 shadow-sm">
            <p className="text-xs text-gray-500">
              Check-in
            </p>

            <p className="text-2xl font-bold text-[#1A5632] mt-2">
              9:00 PM
            </p>
          </div>

          <div className="bg-white rounded-3xl p-5 shadow-sm">
            <p className="text-xs text-gray-500">
              Contacts
            </p>

            <p className="text-2xl font-bold text-[#1A5632] mt-2">
              1 Active
            </p>
          </div>

        </div>

        {/* REMINDER */}
        <section className="bg-gradient-to-r from-[#1A5632] to-[#2F6A44] rounded-3xl p-5 text-white">

          <div className="flex items-center gap-2">
            <Bell size={18} />
            <span className="text-sm">
              Reminder in 2 Hours
            </span>
          </div>

          <button className="w-full mt-4 py-3 rounded-2xl bg-white text-[#1A5632] font-semibold">
            Check In Now
          </button>

        </section>

        {/* REQUIRED SETUP */}
        <section className="bg-white rounded-3xl p-5 shadow-sm">

          <div className="flex items-center gap-2 mb-4">
            <AlertCircle
              size={18}
              className="text-yellow-500"
            />
            <h3 className="font-bold text-[#1A5632]">
              Required Setup
            </h3>
          </div>

          <div className="space-y-3">

            <button className="w-full flex items-center justify-between p-3 rounded-xl bg-gray-50">
              <span>Enable Location</span>
              <ChevronRight size={16} />
            </button>

            <button className="w-full flex items-center justify-between p-3 rounded-xl bg-gray-50">
              <span>Add Home Zone</span>
              <ChevronRight size={16} />
            </button>

            <button className="w-full flex items-center justify-between p-3 rounded-xl bg-gray-50">
              <span>Create Duress PIN</span>
              <ChevronRight size={16} />
            </button>

          </div>

        </section>

        {/* PASSIVE ZONES */}
        <section className="bg-white rounded-3xl p-5 shadow-sm flex items-center gap-4">

          <div className="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center">
            <MapPin size={20} />
          </div>

          <div>
            <h3 className="font-semibold text-[#1A5632]">
              Passive Zones Active
            </h3>

            <p className="text-sm text-gray-500">
              Home • Work • Gym
            </p>
          </div>

        </section>

        {/* PROMOTION */}
        <section className="bg-gradient-to-r from-[#FFF6E0] to-white rounded-3xl p-5 border border-[#D4A017]/20">

          <div className="flex items-center gap-3">

            <div className="w-12 h-12 rounded-xl bg-[#1A5632] flex items-center justify-center">
              <Gift className="text-white" size={20} />
            </div>

            <div>
              <h3 className="font-bold text-[#1A5632]">
                Invite Family & Friends
              </h3>

              <p className="text-sm text-gray-600">
                Get 3 months of KIN Premium free
              </p>
            </div>

          </div>

        </section>

      </main>

      {/* SOS BUTTON */}
      <button className="fixed bottom-20 left-1/2 -translate-x-1/2 w-16 h-16 rounded-full bg-red-600 shadow-xl flex items-center justify-center">
        <ShieldAlert className="text-white" size={28} />
      </button>

      {/* BOTTOM NAV */}
      <nav className="fixed bottom-0 left-0 right-0 h-16 bg-white border-t border-gray-100 flex items-center justify-around">

        <button className="text-[#1A5632] font-medium text-sm">
          Home
        </button>

        <button className="text-gray-400 text-sm">
          Network
        </button>

        <button className="text-gray-400 text-sm">
          Alerts
        </button>

        <button className="text-gray-400 text-sm">
          Profile
        </button>

      </nav>

    </div>
  );
}
