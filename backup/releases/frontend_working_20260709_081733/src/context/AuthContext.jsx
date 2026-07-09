import { createContext, useContext, useState, useEffect } from 'react';

const AuthContext = createContext(null);

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [token, setToken] = useState(null);
    const [loading, setLoading] = useState(true);

    // Load user from localStorage on mount
    useEffect(() => {
        const storedUser = localStorage.getItem('kin_user');
        const storedToken = localStorage.getItem('kin_token');
        
        if (storedUser && storedToken) {
            try {
                setUser(JSON.parse(storedUser));
                setToken(storedToken);
            } catch (e) {
                localStorage.removeItem('kin_user');
                localStorage.removeItem('kin_token');
            }
        }
        setLoading(false);
    }, []);

    const login = (userData, authToken) => {
        setUser(userData);
        setToken(authToken);
        localStorage.setItem('kin_user', JSON.stringify(userData));
        localStorage.setItem('kin_token', authToken);
    };

    const logout = () => {
        setUser(null);
        setToken(null);
        localStorage.removeItem('kin_user');
        localStorage.removeItem('kin_token');
    };

    const isAuthenticated = () => {
        return !!token && !!user;
    };

    return (
        <AuthContext.Provider value={{ 
            user, 
            setUser, 
            token, 
            setToken, 
            loading,
            login, 
            logout, 
            isAuthenticated 
        }}>
            {children}
        </AuthContext.Provider>
    );
};

export const useAuth = () => {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error('useAuth must be used within an AuthProvider');
    }
    return context;
};
