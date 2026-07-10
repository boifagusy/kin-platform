import React from 'react';
import safetySyncManager from './services/SafetySyncManager.js';
import { RouterProvider } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import router from './router';
import './index.css';

useEffect(() => {
  safetySyncManager.start();
  return () => safetySyncManager.stop();
}, []);

function App() {
  useEffect(() => {
    safetySyncManager.start();
    return () => safetySyncManager.stop();
  }, []);
  useEffect(() => {
    const cleanup = initializeSync();
    return cleanup;
  }, []);
    return (
        <AuthProvider>
            <RouterProvider router={router} />
        </AuthProvider>
    );
}

export default App;
