import { useAuth } from '../context/AuthContext';

export function useAuthHeaders() {
    const { token } = useAuth();

    return {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    };
}
