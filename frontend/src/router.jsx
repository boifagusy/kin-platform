import { createBrowserRouter } from 'react-router-dom';
import App from './App';
import ProtectedRoute from './components/auth/ProtectedRoute';
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
            // Public routes
            { index: true, element: <WelcomeScreenV3 /> },
            { path: 'phone', element: <PhoneEntryScreenV2 /> },
            { path: 'create-pin', element: <CreatePinScreenV2 /> },
            { path: 'login-pin', element: <LoginPinScreenV2 /> },
            { path: 'login', element: <LoginPinScreenV2 /> },
            
            // Protected routes
            { 
                path: 'user-details', 
                element: <ProtectedRoute><UserDetailsScreenV2 /></ProtectedRoute> 
            },
            { 
                path: 'trusted-contacts', 
                element: <ProtectedRoute><TrustedContactScreenV2 /></ProtectedRoute> 
            },
            { 
                path: 'duress-pin', 
                element: <ProtectedRoute><DuressPinSetupScreenV2 /></ProtectedRoute> 
            },
            { 
                path: 'dashboard', 
                element: <ProtectedRoute><DashboardScreenV2 /></ProtectedRoute> 
            },
            { 
            {
                path: 'settings',
                element: <ProtectedRoute><SettingsScreen /></ProtectedRoute>,
            },
                path: 'settings/check-in', 
            },
            {
                path: 'settings/safe-zones',
                element: <ProtectedRoute><SafeZonesScreen /></ProtectedRoute>
                element: <ProtectedRoute><CheckInSettingsScreen /></ProtectedRoute> 
            },
            { 
                path: 'continue-setup', 
                element: <ProtectedRoute><ContinueSetupScreen /></ProtectedRoute> 
            },
            { 
                path: 'alerts', 
                element: <ProtectedRoute><AlertsScreenV2 /></ProtectedRoute> 
            },
            { 
                path: 'alerts/:id', 
                element: <ProtectedRoute><AlertDetailScreenV2 /></ProtectedRoute> 
            },
        ],
    },
]);

export default router;
