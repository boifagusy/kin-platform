import { AuthProvider } from './context/AuthContext';
import App from './App.tsx';

export default function AppWithAuth() {
  return (
    <AuthProvider>
      <App />
    </AuthProvider>
  );
}
