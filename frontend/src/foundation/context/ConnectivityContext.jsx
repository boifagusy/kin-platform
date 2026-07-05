// KIN Platform — Connectivity Context
// Provides connection state to all components

import React, { createContext, useContext, useState, useEffect } from 'react';

const ConnectivityContext = createContext(null);

export const ConnectivityProvider = ({ children }) => {
    const [state, setState] = useState({
        status: 'unknown',
        isConnected: false,
        isOffline: false,
        isDegraded: false,
        lastChecked: null,
        diagnostics: null,
    });

    // Placeholder - will be implemented in Block 5
    useEffect(() => {
        setState({
            status: 'initialized',
            isConnected: true,
            isOffline: false,
            isDegraded: false,
            lastChecked: new Date().toISOString(),
            diagnostics: null,
        });
    }, []);

    return (
        <ConnectivityContext.Provider value={state}>
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
