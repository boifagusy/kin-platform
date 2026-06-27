import { createBrowserRouter } from 'react-router-dom';
import App from './App';
import WelcomeScreenV3 from './screens/ui-polish/WelcomeScreenV3';
import PhoneEntryScreenV2 from './screens/ui-polish/PhoneEntryScreenV2';
import CreatePinScreenV2 from './screens/ui-polish/CreatePinScreenV2';
import LoginPinScreenV2 from './screens/ui-polish/LoginPinScreenV2';
import UserDetailsScreenV2 from './screens/ui-polish/UserDetailsScreenV2';
import TrustedContactScreenV2 from './screens/ui-polish/TrustedContactScreenV2';
import DuressPinSetupScreenV2 from './screens/ui-polish/DuressPinSetupScreenV2';
import DashboardScreenV2 from './screens/ui-polish/DashboardScreenV2';
import CheckInSettingsScreen from './screens/settings/CheckInSettingsScreen';
import ContinueSetupScreen from './screens/onboarding/ContinueSetupScreen';

const router = createBrowserRouter([
  {
    path: '/',
    element: <App />,
    children: [
      { index: true, element: <WelcomeScreenV3 /> },
      { path: 'onboarding', element: <PhoneEntryScreenV2 /> },  // 👈 ADDED onboarding route
      { path: 'phone', element: <PhoneEntryScreenV2 /> },
      { path: 'create-pin', element: <CreatePinScreenV2 /> },
      { path: 'login-pin', element: <LoginPinScreenV2 /> },
      { path: 'user-details', element: <UserDetailsScreenV2 /> },
      { path: 'trusted-contacts', element: <TrustedContactScreenV2 /> },
      { path: 'duress-pin', element: <DuressPinSetupScreenV2 /> },
      { path: 'dashboard', element: <DashboardScreenV2 /> },
      { path: 'settings/check-in', element: <CheckInSettingsScreen /> },
      { path: 'continue-setup', element: <ContinueSetupScreen /> },
    ],
  },
]);

export default router;
