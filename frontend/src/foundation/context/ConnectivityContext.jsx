// KIN Platform — Connectivity Context
// Provides connection state to all components

import React, { createContext, useContext, useState, useEffect } from 'react';
import connectionManager from '../connection/ConnectionManager';

const ConnectivityContext = createContext(null);

export const ConnectivityProvider = ({ children }) => {
    const [state, setState] = useState(connectionManager.getState());
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        // Initialize connection manager
        connectionManager.initialize().then(() => {
            setIsLoading(false);
        });

        // Subscribe to state changes
        const unsubscribe = connectionManager.subscribe((newState) => {
            setState(newState);
        });

        return () => {
            unsubscribe();
        };
    }, []);

    const value = {
        ...state,
        isLoading,
        checkHealth: () => connectionManager.checkHealth(),
        retryConnection: () => connectionManager.retryConnection(),
        getDiagnostics: () => connectionManager.getDiagnostics(),
        isConnected: () => connectionManager.isConnected(),
        getApiUrl: () => connectionManager.getApiUrl(),
    };

    return (
        <ConnectivityContext.Provider value={value}>
            {children}
        </ConnectivityContext.Provider>
    );
};

export const useConnectivity = () => {
    const context = useContext(ConnectivityContext);
    if (!context) {
        throw new Error('useConnectivity must be used within ConnectivityProvider');
    }
    return context;
};
