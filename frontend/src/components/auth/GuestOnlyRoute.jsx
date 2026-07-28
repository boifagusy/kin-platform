import { Navigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';

/**
 * GuestOnlyRoute — for routes only an unauthenticated visitor should see.
 *
 * Per ADR-001F: do NOT wrap authentication TRANSITION routes in this guard
 * (routes where the user deliberately moves guest -> authenticated, e.g.
 * /create-pin, /login-pin). This guard reacts to auth state and will
 * pre-empt the screen's own intended navigation mid-flow.
 */
export default function GuestOnlyRoute({ children }) {
    const { isAuthenticated, bootstrapping } = useAuth();

    if (bootstrapping) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-[#F0F7F2]">
                <div className="flex flex-col items-center gap-4">
                    <div className="w-12 h-12 border-4 border-[#1A5632] border-t-transparent rounded-full animate-spin" />
                    <div className="text-[#1A5632] text-sm">Loading...</div>
                </div>
            </div>
        );
    }

    if (isAuthenticated()) {
        return <Navigate to="/dashboard" replace />;
    }

    return children;
}
