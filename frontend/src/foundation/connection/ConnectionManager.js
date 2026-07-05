// KIN Platform — ConnectionManager
// Central connection state management

import configService from '../config/ConfigService';
import FetchClient from './FetchClient';
import HealthMonitor from './HealthMonitor';
import OfflineManager from './OfflineManager';
import DiagnosticsService from './DiagnosticsService';
import EventBus, { Events } from '../events/EventBus';
import connectionMetrics from '../metrics/ConnectionMetrics';

class ConnectionManager {
    constructor() {
        this.state = {
            status: 'unknown',
            isConnected: false,
            isOffline: false,
            isDegraded: false,
            lastChecked: null,
            diagnostics: null,
            environment: null,
            platform: null,
            apiUrl: null,
        };
        this.subscribers = [];
        this.initialized = false;
        this.config = configService;
        this.client = FetchClient;
        this.healthMonitor = null;
        this.offlineManager = null;
        this.diagnostics = null;
        this.eventBus = EventBus;
        this.metrics = connectionMetrics;
    }

    async initialize() {
        if (this.initialized) {
            return;
        }

        // Get config
        const config = this.config.getConfig();
        this.state.environment = config.env;
        this.state.platform = config.platform;
        this.state.apiUrl = this.config.getApiUrl();

        // Update fetch client
        this.client.setBaseURL(this.config.getBackendUrl());

        // Initialize offline manager
        this.offlineManager = new OfflineManager({
            onOnline: () => this.handleOnline(),
            onOffline: () => this.handleOffline(),
        });

        // Initialize diagnostics
        this.diagnostics = new DiagnosticsService();

        // Initialize health monitor
        const healthConfig = this.config.getHealthConfig();
        this.healthMonitor = new HealthMonitor({
            interval: healthConfig.interval,
            timeout: healthConfig.timeout,
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

        // Publish boot event
        this.eventBus.publish(Events.SYSTEM_BOOT, {
            environment: config.env,
            platform: config.platform,
            apiUrl: this.state.apiUrl,
        });

        this.initialized = true;
        this.notifySubscribers();
    }

    getState() {
        return { ...this.state };
    }

    setState(updates) {
        this.state = { ...this.state, ...updates };
        this.notifySubscribers();
    }

    subscribe(callback) {
        this.subscribers.push(callback);
        return () => {
            this.subscribers = this.subscribers.filter(cb => cb !== callback);
        };
    }

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

    handleHealthy() {
        this.setState({
            status: 'connected',
            isConnected: true,
            isOffline: false,
            isDegraded: false,
            lastChecked: new Date().toISOString(),
        });
        this.eventBus.publish(Events.HEALTH_HEALTHY, { timestamp: Date.now() });
        this.metrics.recordSuccess(0);
    }

    handleDegraded(data) {
        this.setState({
            status: 'degraded',
            isConnected: true,
            isOffline: false,
            isDegraded: true,
            lastChecked: new Date().toISOString(),
            diagnostics: data,
        });
        this.eventBus.publish(Events.HEALTH_DEGRADED, { data, timestamp: Date.now() });
    }

    handleUnhealthy(data) {
        this.setState({
            status: 'error',
            isConnected: false,
            isOffline: false,
            isDegraded: false,
            lastChecked: new Date().toISOString(),
            diagnostics: data,
        });
        this.eventBus.publish(Events.HEALTH_UNHEALTHY, { data, timestamp: Date.now() });
        this.metrics.recordFailure(new Error('Backend unhealthy'));
    }

    handleOnline() {
        this.setState({ isOffline: false });
        this.eventBus.publish(Events.CONNECTION_ONLINE, { timestamp: Date.now() });
        this.healthMonitor.checkNow();
    }

    handleOffline() {
        this.setState({
            status: 'offline',
            isConnected: false,
            isOffline: true,
            isDegraded: false,
            lastChecked: new Date().toISOString(),
        });
        this.eventBus.publish(Events.CONNECTION_OFFLINE, { timestamp: Date.now() });
    }

    async checkHealth() {
        if (this.healthMonitor) {
            const startTime = Date.now();
            const result = await this.healthMonitor.checkNow();
            const duration = Date.now() - startTime;
            this.metrics.recordLatency(duration);
            return result;
        }
        return null;
    }

    getDiagnostics() {
        if (this.diagnostics) {
            return this.diagnostics.collect();
        }
        return null;
    }

    async retryConnection() {
        this.setState({ status: 'connecting' });
        this.eventBus.publish(Events.CONNECTION_RETRY, { timestamp: Date.now() });
        this.metrics.recordReconnect();
        await this.healthMonitor.checkNow();
    }

    isConnected() {
        return this.state.isConnected && !this.state.isOffline;
    }

    getApiUrl() {
        return this.state.apiUrl;
    }

    getEnvironment() {
        return this.state.environment;
    }

    getPlatform() {
        return this.state.platform;
    }

    getMetrics() {
        return this.metrics.getMetrics();
    }
}

const connectionManager = new ConnectionManager();
export default connectionManager;
