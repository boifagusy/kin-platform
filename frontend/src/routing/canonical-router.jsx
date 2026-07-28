import { createBrowserRouter } from 'react-router-dom';

// Guard infrastructure (imported but not applied yet - SEC-ROUTER-001C)
import RootLayout from '../RootLayout';
import ProtectedRoute from '../components/auth/ProtectedRoute';
import VersionGate from '../components/auth/VersionGate';

// Onboarding screens
import ContinueSetupScreen from '../screens/onboarding/ContinueSetupScreen';

// UI Polish screens (primary versions)
import WelcomeScreenV3 from '../screens/ui-polish/WelcomeScreenV3';
import PhoneEntryScreenV2 from '../screens/ui-polish/PhoneEntryScreenV2';
import LoginPinScreenV2 from '../screens/ui-polish/LoginPinScreenV2';
import CreatePinScreenV2 from '../screens/ui-polish/CreatePinScreenV2';
import UserDetailsScreenV2 from '../screens/ui-polish/UserDetailsScreenV2';
import CheckInSettingsScreen from '../screens/settings/CheckInSettingsScreen';
import DashboardScreenV2 from '../screens/ui-polish/DashboardScreenV2';
import DuressPinSetupScreenV2 from '../screens/ui-polish/DuressPinSetupScreenV2';
import NetworkScreenV2 from '../screens/ui-polish/NetworkScreenV2';
import AlertsScreenV2 from '../screens/ui-polish/AlertsScreenV2';
import AlertDetailScreenV2 from '../screens/ui-polish/AlertDetailScreenV2';
import ForgotPinScreenV3 from '../screens/ui-polish/ForgotPinScreenV3';
import ProfileScreen from '../screens/profile/ProfileScreen';
import SafeZonesScreen from '../screens/settings/SafeZonesScreen';
import MapScreen from '../screens/map/MapScreen';
import TrustedContactScreenV2 from '../screens/ui-polish/TrustedContactScreenV2';

// Development/test screens
import TailwindTest from '../pages/TailwindTest';
import WelcomeScreenV2 from '../screens/ui-polish/WelcomeScreenV2';
import SplashScreenV2 from '../screens/ui-polish/SplashScreenV2';
import LoadingScreen from '../components/ui/LoadingScreen';

// Canonical routing structure using createBrowserRouter
// All 29 routes from App.tsx, transcribed 1:1 with no guards applied yet
const canonicalRouter = createBrowserRouter([
  {
    path: '/',
    element: <WelcomeScreenV3 />,
  },
  {
    path: '/continue-setup',
    element: <ContinueSetupScreen />,
  },
  {
    path: '/login',
    element: <PhoneEntryScreenV2 />,
  },
  {
    path: '/login-pin',
    element: <LoginPinScreenV2 />,
  },
  {
    path: '/create-pin',
    element: <CreatePinScreenV2 />,
  },
  {
    path: '/user-details',
    element: <UserDetailsScreenV2 />,
  },
  {
    path: '/checkin-settings',
    element: <CheckInSettingsScreen />,
  },
  {
    path: '/dashboard',
    element: <DashboardScreenV2 />,
  },
  {
    path: '/tailwind-test',
    element: <TailwindTest />,
  },
  {
    path: '/settings/checkin',
    element: <CheckInSettingsScreen />,
  },
  {
    path: '/settings/duress-pin',
    element: <DuressPinSetupScreenV2 />,
  },
  {
    path: '/settings/safe-zones',
    element: <SafeZonesScreen />,
  },
  {
    path: '/network',
    element: <NetworkScreenV2 />,
  },
  {
    path: '/alerts',
    element: <AlertsScreenV2 />,
  },
  {
    path: '/forgot-pin',
    element: <ForgotPinScreenV3 />,
  },
  {
    path: '/profile',
    element: <ProfileScreen />,
  },
  {
    path: '/map',
    element: <MapScreen />,
  },
  {
    path: '/ui-lab/welcome-v2',
    element: <WelcomeScreenV2 />,
  },
  {
    path: '/ui-lab/splash-v2',
    element: <SplashScreenV2 />,
  },
  {
    path: '/ui-lab/loading-v2',
    element: <LoadingScreen message="default" />,
  },
  {
    path: '/ui-lab/phone-entry-v2',
    element: <PhoneEntryScreenV2 />,
  },
  {
    path: '/ui-lab/login-pin-v2',
    element: <LoginPinScreenV2 />,
  },
  {
    path: '/ui-lab/create-pin-v2',
    element: <CreatePinScreenV2 />,
  },
  {
    path: '/ui-lab/user-details-v2',
    element: <UserDetailsScreenV2 />,
  },
  {
    path: '/ui-lab/trusted-contact-v2',
    element: <TrustedContactScreenV2 />,
  },
  {
    path: '/ui-lab/duress-pin-v2',
    element: <DuressPinSetupScreenV2 />,
  },
  {
    path: '/ui-lab/dashboard-v2',
    element: <DashboardScreenV2 />,
  },
  {
    path: '/ui-lab/network-v2',
    element: <NetworkScreenV2 />,
  },
  {
    path: '/alert-detail',
    element: <AlertDetailScreenV2 />,
  },
]);

export default canonicalRouter;
