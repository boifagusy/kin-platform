// KIN Platform — ConnectionManager
// Central connection state management

import EnvironmentManager from './EnvironmentManager';
import FetchClient from './FetchClient';
import HealthMonitor from './HealthMonitor';
import OfflineManager from './OfflineManager';
import DiagnosticsService from './DiagnosticsService';

class ConnectionManager {
    constructor() {
        this.state = {
            status: 'unknown', // 'unknown', 'connecting', 'connected', 'degraded', 'offline', 'error'
            isConnected: false,
            isOffline: false,
            isDegraded: false,
            lastChecked: null,
            diagnostics: null,
            environment: null,
            apiUrl: null,
        };
        this.subscribers = [];
        this.initialized = false;
        this.environment = EnvironmentManager;
        this.client = FetchClient;
        this.healthMonitor = null;
        this.offlineManager = null;
        this.diagnostics = null;
    }

    // Initialize the connection manager
    async initialize() {
        if (this.initialized) {
            return;
        }

        // Detect environment
        const env = this.environment.detect();
        this.state.environment = env.name;
        this.state.apiUrl = this.environment.getApiUrl();

        // Update fetch client base URL
        this.client.setBaseURL(this.environment.getApiUrl());

        // Initialize offline manager
        this.offlineManager = new OfflineManager({
            onOnline: () => this.handleOnline(),
            onOffline: () => this.handleOffline(),
        });

        // Initialize diagnostics
        this.diagnostics = new DiagnosticsService();

        // Initialize health monitor
        this.healthMonitor = new HealthMonitor({
            onHealthy: () => this.handleHealthy(),
            onDegraded: (data) => this.handleDegraded(data),
            onUnhealthy: (data) => this.handleUnhealthy(data),
        });

        // Start health monitoring
        await this.healthMonitor.start();

        // Set initial state
        this.setState({
            status: 'connected',
            isConnected: true,
            isOffline: false,
            isDegraded: false,
            lastChecked: new Date().toISOString(),
        });

        this.initialized = true;
        this.notifySubscribers();
    }

    // Get current state
    getState() {
        return { ...this.state };
    }

    // Set state and notify subscribers
    setState(updates) {
        this.state = { ...this.state, ...updates };
        this.notifySubscribers();
    }

    // Subscribe to state changes
    subscribe(callback) {
        this.subscribers.push(callback);
        return () => {
            this.subscribers = this.subscribers.filter(cb => cb !== callback);
        };
    }

    // Notify all subscribers
    notifySubscribers() {
        const state = this.getState();
        for (const callback of this.subscribers) {
            try {
                callback(state);
            } catch (error) {
                console.error('Error in subscriber:', error);
            }
        }
    }

    // Handle healthy status
    handleHealthy() {
        this.setState({
            status: 'connected',
            isConnected: true,
            isOffline: false,
            isDegraded: false,
            lastChecked: new Date().toISOString(),
        });
    }

    // Handle degraded status
    handleDegraded(data) {
        this.setState({
            status: 'degraded',
            isConnected: true,
            isOffline: false,
            isDegraded: true,
            lastChecked: new Date().toISOString(),
            diagnostics: data,
        });
    }

    // Handle unhealthy status
    handleUnhealthy(data) {
        this.setState({
            status: 'error',
            isConnected: false,
            isOffline: false,
            isDegraded: false,
            lastChecked: new Date().toISOString(),
            diagnostics: data,
        });
    }

    // Handle online event
    handleOnline() {
        this.setState({
            isOffline: false,
        });
        // Try to reconnect
        this.healthMonitor.checkNow();
    }

    // Handle offline event
    handleOffline() {
        this.setState({
            status: 'offline',
            isConnected: false,
            isOffline: true,
            isDegraded: false,
            lastChecked: new Date().toISOString(),
        });
    }

    // Manual health check
    async checkHealth() {
        if (this.healthMonitor) {
            return await this.healthMonitor.checkNow();
        }
        return null;
    }

    // Get diagnostics
    getDiagnostics() {
        if (this.diagnostics) {
            return this.diagnostics.collect();
        }
        return null;
    }

    // Retry connection
    async retryConnection() {
        this.setState({
            status: 'connecting',
        });
        await this.healthMonitor.checkNow();
    }

    // Check if connected
    isConnected() {
        return this.state.isConnected && !this.state.isOffline;
    }

    // Get API URL
    getApiUrl() {
        return this.state.apiUrl;
    }

    // Get environment
    getEnvironment() {
        return this.state.environment;
    }
}

// Singleton instance
const connectionManager = new ConnectionManager();
export default connectionManager;
