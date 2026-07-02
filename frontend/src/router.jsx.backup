import React from 'react'
import { createBrowserRouter } from 'react-router-dom'
import PhoneEntryScreen from './screens/auth/PhoneEntryScreen'
import CreatePinScreen from './screens/auth/CreatePinScreen'
import LoginPinScreen from './screens/auth/LoginPinScreen'
import UserDetailsScreen from './screens/auth/UserDetailsScreen'
import TrustedContactScreen from './screens/auth/TrustedContactScreen'
import DashboardScreenV2 from './screens/ui-polish/DashboardScreenV2'
import TestDashboard from './screens/watchtower/TestDashboard'

const router = createBrowserRouter([
  {
    path: '/',
    element: <PhoneEntryScreen />,
  },
  {
    path: '/create-pin',
    element: <CreatePinScreen />,
  },
  {
    path: '/login-pin',
    element: <LoginPinScreen />,
  },
  {
    path: '/user-details',
    element: <UserDetailsScreen />,
  },
  {
    path: '/trusted-contacts',
    element: <TrustedContactScreen />,
  },
  {
    path: '/dashboard',
    element: <DashboardScreenV2 />,
  },
  {
    path: '/watchtower',
    element: <TestDashboard />,
  },
])

export default router
