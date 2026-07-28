import {
  BrowserRouter,
  Routes,
  Route,
} from "react-router-dom";

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
import ProtectedRoute from './components/auth/ProtectedRoute';
import GuestOnlyRoute from './components/auth/GuestOnlyRoute';
import DuressPinSetupScreenV2 from "./screens/ui-polish/DuressPinSetupScreenV2";
import NetworkScreenV2 from "./screens/ui-polish/NetworkScreenV2";
import AlertsScreen from "./screens/alerts/AlertsScreen";
import AlertsScreenV2 from "./screens/ui-polish/AlertsScreenV2";
import AlertDetailScreenV2 from "./screens/ui-polish/AlertDetailScreenV2";
import ForgotPinScreenV3 from "./screens/ui-polish/ForgotPinScreenV3";
import ProfileScreen from "./screens/profile/ProfileScreen";
import SafeZonesScreen from "./screens/settings/SafeZonesScreen";
import MapScreen from "./screens/map/MapScreen";

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/continue-setup" element={<ContinueSetupScreen />} />
        <Route path="/" element={<WelcomeScreenV3 />} />
        <Route path="/login" element={<GuestOnlyRoute><PhoneEntryScreenV2 /></GuestOnlyRoute>} />
        {/* ADR-001F: transition route (guest -> authenticated) — must NOT use GuestOnlyRoute */}
        <Route path="/login-pin" element={<LoginPinScreenV2 />} />
        {/* ADR-001F: transition route (guest -> authenticated) — must NOT use GuestOnlyRoute */}
        <Route path="/create-pin" element={<CreatePinScreenV2 />} />
        <Route path="/user-details" element={<ProtectedRoute><UserDetailsScreenV2 /></ProtectedRoute>} />
        <Route path="/checkin-settings" element={<ProtectedRoute><CheckInSettingsScreen /></ProtectedRoute>} />
        <Route path="/dashboard" element={<ProtectedRoute><DashboardScreenV2 /></ProtectedRoute>} />
        <Route path="/tailwind-test" element={<TailwindTest />} />
        <Route path="/settings/checkin" element={<ProtectedRoute><CheckInSettingsScreen /></ProtectedRoute>} />
        <Route path="/settings/duress-pin" element={<ProtectedRoute><DuressPinSetupScreenV2 /></ProtectedRoute>} />
        <Route path="/settings/safe-zones" element={<ProtectedRoute><SafeZonesScreen /></ProtectedRoute>} />
        <Route path="/network" element={<ProtectedRoute><NetworkScreenV2 /></ProtectedRoute>} />
        <Route path="/alerts" element={<ProtectedRoute><AlertsScreenV2 /></ProtectedRoute>} />
        <Route path="/forgot-pin" element={<GuestOnlyRoute><ForgotPinScreenV3 /></GuestOnlyRoute>} />
        <Route path="/profile" element={<ProtectedRoute><ProfileScreen /></ProtectedRoute>} />
        <Route path="/map" element={<ProtectedRoute><MapScreen /></ProtectedRoute>} />
        {/* @dev-only - Test/comparison routes, excluded from production */}
        <Route path="/ui-lab/welcome-v2" element={<WelcomeScreenV2 />} />
        <Route path="/ui-lab/splash-v2" element={<SplashScreenV2 />} />
        <Route path="/ui-lab/loading-v2" element={<LoadingScreen message="default" />} />
        <Route path="/ui-lab/phone-entry-v2" element={<PhoneEntryScreenV2 />} />
        <Route path="/ui-lab/login-pin-v2" element={<LoginPinScreenV2 />} />
        <Route path="/ui-lab/create-pin-v2" element={<CreatePinScreenV2 />} />
        <Route path="/ui-lab/user-details-v2" element={<UserDetailsScreenV2 />} />
        <Route path="/ui-lab/trusted-contact-v2" element={<TrustedContactScreenV2 />} />
        <Route path="/ui-lab/duress-pin-v2" element={<DuressPinSetupScreenV2 />} />
        <Route path="/ui-lab/dashboard-v2" element={<DashboardScreenV2 />} />
        <Route path="/ui-lab/network-v2" element={<NetworkScreenV2 />} />
        <Route path="/alert-detail" element={<ProtectedRoute><AlertDetailScreenV2 /></ProtectedRoute>} />
      </Routes>
    </BrowserRouter>
  );
}
