import { createBrowserRouter } from 'react-router-dom';
import App from './App';

// Simple test component
const Home = () => (
  <div style={{ padding: '40px', textAlign: 'center' }}>
    <h1 style={{ color: '#1A5632' }}>✅ Router Working</h1>
    <p>If you see this, the router is loading.</p>
  </div>
);

const router = createBrowserRouter([
  {
    path: '/',
    element: <App />,
    children: [
      { index: true, element: <Home /> },
    ],
  },
]);

export default router;
