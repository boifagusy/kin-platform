import { createBrowserRouter } from 'react-router-dom';
import App from './App';
import ProtectedRoute from './components/auth/ProtectedRoute';
import VersionGate from './components/auth/VersionGate';
import WelcomeScreenV3 from './screens/ui-polish/WelcomeScreenV3';
import PhoneEntryScreenV2 from './screens/ui-polish/PhoneEntryScreenV2';
import CreatePinScreenV2 from './screens/ui-polish/CreatePinScreenV2';
import LoginPinScreenV2 from './screens/ui-polish/LoginPinScreenV2';
import UserDetailsScreenV2 from './screens/ui-polish/UserDetailsScreenV2';
import TrustedContactScreenV2 from './screens/ui-polish/TrustedContactScreenV2';
import DuressPinSetupScreenV2 from './screens/ui-polish/DuressPinSetupScreenV2';
import DashboardScreenV2 from './screens/ui-polish/DashboardScreenV2';
import SettingsScreen from './screens/settings/SettingsScreen';
import SafeZonesScreen from './screens/settings/SafeZonesScreen';
import CheckInSettingsScreen from './screens/settings/CheckInSettingsScreen';
import ContinueSetupScreen from './screens/onboarding/ContinueSetupScreen';
import AlertsScreenV2 from './screens/ui-polish/AlertsScreenV2';
import AlertDetailScreenV2 from './screens/ui-polish/AlertDetailScreenV2';

const router = createBrowserRouter([
    {
        path: '/',
        element: <App />,
        children: [
            // Public routes (no version gate)
            { index: true, element: <WelcomeScreenV3 /> },
            { path: 'phone', element: <PhoneEntryScreenV2 /> },
            { path: 'create-pin', element: <CreatePinScreenV2 /> },
            { path: 'login-pin', element: <LoginPinScreenV2 /> },
            { path: 'login', element: <LoginPinScreenV2 /> },

            // Protected routes with version enforcement
            {
                path: 'user-details',
                element: <VersionGate><ProtectedRoute><UserDetailsScreenV2 /></ProtectedRoute></VersionGate>
            },
            {
                path: 'trusted-contacts',
                element: <VersionGate><ProtectedRoute><TrustedContactScreenV2 /></ProtectedRoute></VersionGate>
            },
            {
                path: 'duress-pin',
                element: <VersionGate><ProtectedRoute><DuressPinSetupScreenV2 /></ProtectedRoute></VersionGate>
            },
            {
                path: 'dashboard',
                element: <VersionGate><ProtectedRoute><DashboardScreenV2 /></ProtectedRoute></VersionGate>
            },
            {
                path: "settings",
                element: <VersionGate><ProtectedRoute><SettingsScreen /></ProtectedRoute></VersionGate>,
            },
            {
                path: "settings/check-in",
                element: <VersionGate><ProtectedRoute><CheckInSettingsScreen /></ProtectedRoute></VersionGate>,
            },
            {
                path: "settings/safe-zones",
                element: <VersionGate><SafeZonesScreen /></VersionGate>,
            },
            {
                path: 'continue-setup',
                element: <VersionGate><ProtectedRoute><ContinueSetupScreen /></ProtectedRoute></VersionGate>
            },
            {
                path: 'alerts',
                element: <VersionGate><ProtectedRoute><AlertsScreenV2 /></ProtectedRoute></VersionGate>
            },
            {
                path: 'alerts/:id',
                element: <VersionGate><ProtectedRoute><AlertDetailScreenV2 /></ProtectedRoute></VersionGate>
            },
        ],
    },
]);

export default router;
