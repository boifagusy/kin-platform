import { useState, useEffect } from "react";
import { useNavigate, useLocation } from "react-router-dom";

function DashboardScreen() {
  const navigate = useNavigate();
  const location = useLocation();
  const phone = location.state?.phone;
  
  const [locationEnabled, setLocationEnabled] = useState(false);
  const [userName, setUserName] = useState("Amara");
  
  useEffect(() => {
    // Fetch user name from backend
    if (phone) {
      fetch(`http://127.0.0.1:8000/api/v1/user/${phone}`)
        .then(res => res.json())
        .then(data => {
          if (data.name && data.name !== "Kin User") {
            setUserName(data.name.split(' ')[0]);
          }
        })
        .catch(err => console.error("Error fetching user:", err));
    }
  }, [phone]);
  
  return (
    <div className="bg-background text-on-background min-h-screen pb-[100px]">
      {/* TopAppBar */}
      <header className="fixed top-0 w-full z-50 bg-surface shadow-sm h-nav-height px-md flex justify-between items-center">
        <div className="flex items-center gap-sm">
          <div className="w-10 h-10 rounded-full border-2 border-secondary overflow-hidden bg-primary-container flex items-center justify-center">
            <img 
              alt="Profile" 
              className="w-full h-full object-cover" 
              src="https://lh3.googleusercontent.com/aida-public/AB6AXuCA0A28PzBO5DKKx-p4pX1Gbs3H8XLpfdRYEYigfIBmFMtB_rm-b6RHpVFo2kATXSqIy7Nt2oPNEtVdKy0KcQmcW5G0tWCj5U3_ZI95KWZ0l-aamIG-OxGwgyN9JDSo4ki8k2wpEJgFhjOqZ_pzfOn5rgKj4EB57V9DIDX-qv7lkkC5XQiegxpA0CgjQB7s_6ZcNml8nsPevazDNURes21AhqN-kJgmSx2TnjuQhr7Kmrpzm9hDfhU2zEJw_ycOsGttMPBmoEz0ZXnV"
            />
          </div>
          <h1 className="font-headline-sm text-headline-sm text-text-primary">Hi, {userName}</h1>
        </div>
        <div className="flex items-center gap-md">
          <button className="relative p-base active:scale-95 duration-150">
            <span className="material-symbols-outlined text-primary text-[28px]">notifications</span>
            <span className="absolute top-1 right-1 w-2.5 h-2.5 bg-error rounded-full border-2 border-surface"></span>
          </button>
        </div>
      </header>
      
      <main className="pt-[80px] px-md space-y-md">
        {/* Safety Streak Card */}
        <section className="safety-gradient rounded-xl p-lg shadow-md flex justify-between items-center text-on-primary">
          <div>
            <p className="font-label-md text-label-md opacity-90">Safety Streak</p>
            <div className="flex items-center gap-xs mt-base">
              <span className="text-[32px]">🔥</span>
              <span className="font-display text-display text-white">7 days</span>
            </div>
          </div>
          <div className="bg-white/20 p-sm rounded-full">
            <span className="material-symbols-outlined text-white text-[32px]" style={{ fontVariationSettings: "'FILL' 1" }}>shield_with_heart</span>
          </div>
        </section>
        
        {/* Status Cards Grid */}
        <div className="grid grid-cols-1 gap-sm">
          <div className="bg-surface-white rounded-xl p-md shadow-sm border border-outline-variant/30 flex items-center gap-md active:scale-[0.98] transition-transform">
            <div className="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
              <span className="material-symbols-outlined text-primary font-bold" style={{ fontVariationSettings: "'FILL' 1" }}>check_circle</span>
            </div>
            <div>
              <h3 className="font-body-sm text-body-sm text-text-primary">Last check-in 2 hours ago</h3>
              <p className="font-label-md text-label-md text-text-muted">Home • Secured</p>
            </div>
          </div>
          
          <div className="bg-surface-white rounded-xl p-md shadow-sm border border-outline-variant/30 flex items-center gap-md active:scale-[0.98] transition-transform">
            <div className="w-10 h-10 rounded-full bg-secondary/10 flex items-center justify-center">
              <span className="material-symbols-outlined text-secondary" style={{ fontVariationSettings: "'FILL' 1" }}>schedule</span>
            </div>
            <div className="flex-grow">
              <h3 className="font-body-sm text-body-sm text-text-primary">Next check-in in 4 hours</h3>
              <p className="font-label-md text-label-md text-text-muted">Scheduled for 9:00 PM</p>
            </div>
            <span className="material-symbols-outlined text-outline-variant">chevron_right</span>
          </div>
        </div>
        
        {/* Safety Network Section */}
        <section className="py-xs">
          <div className="flex justify-between items-center mb-md">
            <h2 className="font-headline-sm text-[16px] text-text-primary">Your Safety Network</h2>
            <button className="text-primary font-label-md text-label-md font-semibold">View All</button>
          </div>
          <div className="flex gap-lg overflow-x-auto hide-scrollbar pb-xs">
            <div className="flex flex-col items-center gap-xs flex-shrink-0">
              <button className="w-14 h-14 rounded-full border-2 border-dashed border-primary text-primary flex items-center justify-center hover:bg-primary/5 transition-colors">
                <span className="material-symbols-outlined">add</span>
              </button>
              <span className="font-label-md text-label-md text-text-muted">Add</span>
            </div>
            
            <div className="flex flex-col items-center gap-xs flex-shrink-0">
              <div className="w-14 h-14 rounded-full border-2 border-secondary p-0.5">
                <img className="w-full h-full rounded-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCWpC69m7_UOFBm7HnRY7uqW9z-gS8xsx76K79Q9889AeEy1lDK0scGYura8Fr6Yxlat5V6Z5q-Vnte0s5zDTUDgOi4MzBXNz2Rubswzkl_1dWIzEAVlSnCGx5a5Xcii5iNy8ECYWd5MCFRdkn-7BHAEZXRHGIQE5iXDde-oVLYdaCrlxbiZ2nZFO40KOkcoVx2y-b7mCkUoQOkKA1sPeNakHZbJgt40vGw8NjXHCrAmen5N24hUy6IfZdlI-Tyk3M_vYo7Ixsc0E90"/>
              </div>
              <span className="font-label-md text-label-md text-text-primary">Mama</span>
            </div>
            
            <div className="flex flex-col items-center gap-xs flex-shrink-0">
              <div className="w-14 h-14 rounded-full border-2 border-secondary p-0.5">
                <img className="w-full h-full rounded-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDAy4Ez2uM-FFjPUxuyYZ5rFgZGoSNkIQDgM7n3Ve0cAdIrl3741mza1Lmj7-umuzvQ9gXUF_uxJwO3P9JLclpaSyJq1YR7_vNPIYwuZiyy-17_QDS3Ta3g5838qzkKoO4EWM4KULigyV_dPETRqGU47ZNKa7hCdVp7ZDFm8h_KKErTc_dvLL-cNEGa7EW3_u6Y2LcybwQvPZx0_FR0ct8mGsa60SsoUJhyOjZ5cSPgNUvWCABNkEPX5qb23Yz-hIros85n4fX3lw_n"/>
              </div>
              <span className="font-label-md text-label-md text-text-primary">Baba</span>
            </div>
            
            <div className="flex flex-col items-center gap-xs flex-shrink-0">
              <div className="w-14 h-14 rounded-full border-2 border-secondary p-0.5">
                <img className="w-full h-full rounded-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDeI9AnbvyPxne0lT1-O_CHtfOw0M2-LbY0BYiZMRogcZmobtxz7oxgUK34Rs_SeO79yrD3XfkyOL6JaTjgi-JklTkPlRVUVrhju94BxLzldPYn8BzsBgj5tK1CSgSJApKpJrCsYa__MjkpUrR1UFu-JwsS5uKQCSjQpkOD8Nwu7RoYfUudB_Nmy6dcfstM6Rmtbk5DCX4qFzJQS5FiWjAtUmV1YnP8WuwsWgK2F99BdmwDrZ2bj3BVpkxpoe-pLeR5PquqWGNR9OxA"/>
              </div>
              <span className="font-label-md text-label-md text-text-primary">Amara</span>
            </div>
            
            <div className="flex flex-col items-center gap-xs flex-shrink-0">
              <div className="w-14 h-14 rounded-full border-2 border-secondary p-0.5">
                <img className="w-full h-full rounded-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDVKUvOKAPW1r8RwuiKvSopdLLd1v-T9bDL4piITIQPcU6kbG1VC1hMxBSW7dtvJnin-MVdTq3fP-I_VzgBRVHG5A9UyCP-yXRDufdwwesTmxKsezl9R-8dQzuwoojaBPShQE88eF5cDZ-oyKnx6a17N5l9I2UkXox6iaBJDjN0IvfaJz_ZJgM8q0sZK7nBbgyKLOeWRum_LjG9i764zSdw_KuykaLrikeSh5NUpZXAPxKLn5OAODuQpMHyOH65jUieSirYZBo8-ka3"/>
              </div>
              <span className="font-label-md text-label-md text-text-primary">Kofi</span>
            </div>
          </div>
        </section>
        
        {/* Location Card */}
        <section className="bg-surface-white rounded-xl p-md shadow-sm border border-outline-variant/30">
          <div className="flex items-center justify-between mb-xs">
            <div className="flex items-center gap-sm">
              <div className="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center">
                <span className="material-symbols-outlined text-outline">
                  {locationEnabled ? "location_on" : "location_off"}
                </span>
              </div>
              <div>
                <h3 className="font-body-sm text-body-sm font-semibold text-text-primary">
                  Location sharing is {locationEnabled ? "ON" : "OFF"}
                </h3>
                <p className="font-label-md text-label-md text-text-muted">
                  {locationEnabled ? "Emergency alerts active" : "Turn on for emergency alerts"}
                </p>
              </div>
            </div>
            <label className="relative inline-flex items-center cursor-pointer">
              <input 
                type="checkbox" 
                className="sr-only peer" 
                checked={locationEnabled}
                onChange={() => setLocationEnabled(!locationEnabled)}
              />
              <div className="w-11 h-6 bg-surface-container-highest peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
            </label>
          </div>
        </section>
        
        {/* Duress Button */}
        <section className="pt-sm">
          <button className="w-full h-[56px] bg-danger text-on-error rounded-xl shadow-lg flex items-center justify-center gap-sm active:scale-95 transition-all duration-200 uppercase tracking-wide font-bold">
            <span className="material-symbols-outlined text-[24px]">warning</span>
            <span className="font-body-lg text-body-lg">I'M NOT SAFE — Activate Duress</span>
          </button>
          <p className="text-center text-label-md text-text-muted mt-sm px-lg">
            Activating duress mode will immediately notify your safety network and share your live location.
          </p>
        </section>
      </main>
      
      {/* BottomNavBar */}
      <nav className="fixed bottom-0 w-full z-50 h-nav-height bg-surface-white shadow-lg border-t border-outline-variant flex justify-around items-center px-xs">
        <button className="flex flex-col items-center justify-center text-primary font-bold transition-colors">
          <span className="material-symbols-outlined text-[24px]" style={{ fontVariationSettings: "'FILL' 1" }}>home</span>
          <span className="font-label-sm text-label-sm mt-0.5">Home</span>
        </button>
        
        <button className="flex flex-col items-center justify-center text-text-muted hover:text-primary transition-colors">
          <span className="material-symbols-outlined text-[24px]">group</span>
          <span className="font-label-sm text-label-sm mt-0.5">Network</span>
        </button>
        
        <button className="flex flex-col items-center justify-center -mt-6">
          <div className="w-14 h-14 bg-primary rounded-full shadow-lg flex items-center justify-center text-white active:scale-90 duration-200">
            <span className="material-symbols-outlined text-[32px]">verified_user</span>
          </div>
          <span className="font-label-sm text-label-sm mt-1 text-primary font-semibold">Check-in</span>
        </button>
        
        <button className="flex flex-col items-center justify-center text-text-muted hover:text-primary transition-colors">
          <span className="material-symbols-outlined text-[24px]">notifications_active</span>
          <span className="font-label-sm text-label-sm mt-0.5">Alerts</span>
        </button>
        
        <button className="flex flex-col items-center justify-center text-text-muted hover:text-primary transition-colors">
          <span className="material-symbols-outlined text-[24px]">person</span>
          <span className="font-label-sm text-label-sm mt-0.5">Profile</span>
        </button>
      </nav>
    </div>
  );
}

export default DashboardScreen;
