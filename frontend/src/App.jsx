import React, { useEffect } from 'react';
import safetySyncManager from './services/SafetySyncManager.js';
import { RouterProvider } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import router from './router';
import initializeSync from './services/syncInitializer.js';
import './index.css';

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
