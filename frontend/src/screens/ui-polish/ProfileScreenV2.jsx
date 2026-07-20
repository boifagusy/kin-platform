import { useState, useEffect } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import ScreenLayout from '../../design-system/layouts/ScreenLayout';
import Card from '../../design-system/components/Card';
import SectionHeader from '../../design-system/components/SectionHeader';
import SettingRow from '../../design-system/components/SettingRow';
import Button from '../../design-system/components/Button';
import PageMotion from '../../motion/page';

const API_BASE = import.meta.env.VITE_API_URL || "http://localhost:8000";

function ProfileScreenV2() {
  const location = useLocation();
  const navigate = useNavigate();
  const phone = location.state?.phone || localStorage.getItem("kin_phone");

  const [loading, setLoading] = useState(true);
  const [user, setUser] = useState(null);
  const [safetyScore, setSafetyScore] = useState(0);
  const [hasDuressPin, setHasDuressPin] = useState(false);
  const [hasTrustedContact, setHasTrustedContact] = useState(false);
  const [contactsCount, setContactsCount] = useState(0);

  useEffect(() => {
    if (!phone) {
      navigate("/login");
      return;
    }
    fetchUserData();
  }, [phone]);

  const fetchUserData = async () => {
    try {
      const response = await fetch(`${API_BASE}/api/v1/dashboard?phone=${encodeURIComponent(phone)}`);
      const data = await response.json();
      if (data.success) {
        setUser(data.user);
        setSafetyScore(data.safety_score || 0);
        const hasDuressTask = data.pending_tasks?.some(t => t.id === 'duress_pin');
        setHasDuressPin(!hasDuressTask);
        setContactsCount(data.data?.contacts_count || 0);
        setHasTrustedContact(data.data?.has_verified_contact || false);
      }
    } catch (error) {
      console.error("Error fetching user data:", error);
    } finally {
      setLoading(false);
    }
  };

  const handleSignOut = () => {
    localStorage.removeItem('kin_phone');
    navigate("/login");
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-[#F0F7F2] flex items-center justify-center">
        <div className="text-center">
          <div className="w-10 h-10 border-3 border-[#1A5632] border-t-transparent rounded-full animate-spin mx-auto mb-3" />
          <p className="text-[#1A5632] text-sm">Loading profile...</p>
        </div>
      </div>
    );
  }

  const userName = user?.name || "User";
  const userInitial = userName.charAt(0).toUpperCase();

  return (
    <ScreenLayout>
      <PageMotion>
        {/* Header — existing, not M3 AppBar */}
        <div className="bg-white px-5 py-4 border-b border-[#E9ECEF] sticky top-0 z-10">
          <div className="flex items-center gap-4">
            <button onClick={() => navigate(-1)} className="text-[#1A5632]">
              <span className="material-symbols-outlined">arrow_back</span>
            </button>
            <h1 className="text-lg font-bold text-[#1A5632]">Profile</h1>
          </div>
        </div>

        <div className="px-5 pt-2 pb-24 space-y-5 max-w-md mx-auto">
          {/* Profile Card */}
          <Card>
            <div className="text-center">
              <div className="w-24 h-24 rounded-full bg-gradient-to-br from-[#1A5632] to-[#0E3A22] flex items-center justify-center text-white text-3xl font-bold shadow-md mx-auto">
                {userInitial}
              </div>
              <h2 className="text-xl font-bold text-[#1A1A1A] mt-4">{userName}</h2>
              <div className="flex items-center justify-center gap-2 mt-1">
                <div className="w-2 h-2 rounded-full bg-green-500" />
                <span className="text-xs text-[#6C757D]">Active</span>
              </div>
              <div className="mt-4 inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#E8F3EA]">
                <span className="material-symbols-outlined text-[#1A5632] text-sm">shield</span>
                <span className="text-sm font-medium text-[#1A5632]">Safety Score: {safetyScore}%</span>
              </div>
            </div>
          </Card>

          {/* Contact Info */}
          <Card>
            <SettingRow icon="call" label={user?.phone || phone} trailing="value" />
            <SettingRow icon="mail" label={user?.email || "No email set"} trailing="value" />
          </Card>

          {/* Security */}
          <Card>
            <SectionHeader title="Security" />
            <SettingRow
              icon="lock"
              label="Change PIN"
              desc="Update your login PIN"
              onPress={() => navigate("/forgot-pin")}
            />
            <SettingRow
              icon="shield_person"
              label="Duress PIN"
              desc={hasDuressPin ? "Configured" : "Not set"}
              onPress={() => navigate("/settings/duress-pin", { state: { phone } })}
            />
          </Card>

          {/* Safety Settings */}
          <Card>
            <SectionHeader title="Safety Settings" />
            <SettingRow
              icon="schedule"
              label="Check-in Settings"
              desc="Daily check-in time"
              onPress={() => navigate("/checkin-settings", { state: { phone } })}
            />
            <SettingRow
              icon="location_on"
              label="Safe Zones"
              desc="Manage safe locations"
              onPress={() => navigate("/settings/safe-zones")}
            />
          </Card>

          {/* Trusted Contact */}
          <Card>
            <SectionHeader title="Trusted Contact" />
            <SettingRow
              icon="group"
              label="Trusted Contact"
              desc={hasTrustedContact && contactsCount === 1 ? "1 trusted contact" : "No trusted contact added"}
              onPress={() => navigate("/network", { state: { phone } })}
            />
          </Card>

          {/* Sign Out */}
          <Button variant="danger" size="md" onClick={handleSignOut} className="w-full">
            Sign Out
          </Button>

          {/* Version Footer */}
          <p className="text-center text-xs text-[#6C757D] py-4">
            KIN v1.0.0 • Protecting what matters
          </p>
        </div>
      </PageMotion>
    </ScreenLayout>
  );
}

export default ProfileScreenV2;
