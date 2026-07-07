import { BrowserRouter, Routes, Route } from "react-router-dom";
import { UserPreferenceProvider } from "./context/UserPreferenceContext";

import ContinueSetupScreen from "./screens/onboarding/ContinueSetupScreen";
import PhoneEntryScreenV2 from "./screens/ui-polish/PhoneEntryScreenV2";
import CreatePinScreenV2 from "./screens/ui-polish/CreatePinScreenV2";
import UserDetailsScreenV2 from "./screens/ui-polish/UserDetailsScreenV2";
import TrustedContactScreenV2 from "./screens/ui-polish/TrustedContactScreenV2";
import DashboardScreenV2 from "./screens/ui-polish/DashboardScreenV2";
import TailwindTest from "./pages/TailwindTest";
import CheckInSettingsScreen from "./screens/settings/CheckInSettingsScreen";
import LoginPinScreenV2 from "./screens/ui-polish/LoginPinScreenV2";
import WelcomeScreenV2 from "./screens/ui-polish/WelcomeScreenV2";
import WelcomeScreenV3 from "./screens/ui-polish/WelcomeScreenV3";
import SplashScreenV2 from "./screens/ui-polish/SplashScreenV2";
import LoadingScreen from "./components/ui/LoadingScreen";
import DuressPinSetupScreenV2 from "./screens/ui-polish/DuressPinSetupScreenV2";
import NetworkScreenV2 from "./screens/ui-polish/NetworkScreenV2";
import AlertsScreen from "./screens/alerts/AlertsScreen";
import AlertsScreenV2 from "./screens/ui-polish/AlertsScreenV2";
import AlertDetailScreenV2 from "./screens/ui-polish/AlertDetailScreenV2";
import ForgotPinScreenV3 from "./screens/ui-polish/ForgotPinScreenV3";
import ProfileScreen from "./screens/profile/ProfileScreen";
import MapScreen from "./screens/map/MapScreen";
import SafetySettingsScreen from "./screens/settings/SafetySettingsScreen";

export default function App() {
  return (
    <UserPreferenceProvider>
      <BrowserRouter>
        <Routes>
          <Route path="/continue-setup" element={<ContinueSetupScreen />} />
          <Route path="/" element={<WelcomeScreenV3 />} />
          <Route path="/login" element={<PhoneEntryScreenV2 />} />
          <Route path="/login-pin" element={<LoginPinScreenV2 />} />
          <Route path="/create-pin" element={<CreatePinScreenV2 />} />
          <Route path="/user-details" element={<UserDetailsScreenV2 />} />
          <Route path="/checkin-settings" element={<CheckInSettingsScreen />} />
          <Route path="/dashboard" element={<DashboardScreenV2 />} />
          <Route path="/tailwind-test" element={<TailwindTest />} />
          <Route path="/settings/checkin" element={<CheckInSettingsScreen />} />
          <Route path="/settings/duress-pin" element={<DuressPinSetupScreenV2 />} />
          <Route path="/network" element={<NetworkScreenV2 />} />
          <Route path="/alerts" element={<AlertsScreenV2 />} />
          <Route path="/forgot-pin" element={<ForgotPinScreenV3 />} />
          <Route path="/profile" element={<ProfileScreen />} />
          <Route path="/safety-settings" element={<SafetySettingsScreen />} />
        </Routes>
      </BrowserRouter>
    </UserPreferenceProvider>
  );
}
