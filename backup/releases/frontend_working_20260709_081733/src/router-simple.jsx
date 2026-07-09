import { createBrowserRouter } from 'react-router-dom';
import App from './App';

// Simple test component
const Home = () => (
  <div style={{ 
    padding: '40px', 
    textAlign: 'center',
    fontFamily: 'sans-serif',
    minHeight: '100vh',
    background: '#f8f9fa'
  }}>
    <h1 style={{ color: '#1A5632' }}>✅ KIN is Working!</h1>
    <p style={{ color: '#666' }}>If you can see this, React is rendering correctly.</p>
    <button 
      onClick={() => alert('Button works!')}
      style={{
        padding: '12px 24px',
        background: '#1A5632',
        color: 'white',
        border: 'none',
        borderRadius: '8px',
        cursor: 'pointer',
        marginTop: '20px'
      }}
    >
      Click Me
    </button>
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
