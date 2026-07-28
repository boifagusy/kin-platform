import { Navigate } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';

const ProtectedRoute = ({ children }) => {
    const { isAuthenticated, bootstrapping } = useAuth();

    if (bootstrapping) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-[#F0F7F2]">
                <div className="flex flex-col items-center gap-4">
                    <div className="w-12 h-12 border-4 border-[#1A5632] border-t-transparent rounded-full animate-spin" />
                    <div className="text-[#1A5632] text-sm">Restoring session...</div>
                </div>
            </div>
        );
    }

    if (!isAuthenticated()) {
        return <Navigate to="/login" replace />;
    }

    return children;
};

export default ProtectedRoute;
