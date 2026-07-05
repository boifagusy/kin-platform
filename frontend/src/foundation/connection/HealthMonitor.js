// KIN Platform — HealthMonitor
// Polls backend health endpoint

import FetchClient from './FetchClient';

class HealthMonitor {
    constructor(config = {}) {
        this.interval = config.interval || 30000; // 30 seconds
        this.healthEndpoint = config.healthEndpoint || '/health';
        this.timeout = config.timeout || 5000;
        this.onHealthy = config.onHealthy || (() => {});
        this.onDegraded = config.onDegraded || (() => {});
        this.onUnhealthy = config.onUnhealthy || (() => {});
        this.isRunning = false;
        this.timerId = null;
        this.lastResult = null;
    }

    // Start monitoring
    async start() {
        if (this.isRunning) {
            return;
        }

        this.isRunning = true;

        // Do initial check
        await this.checkNow();

        // Start polling
        this.timerId = setInterval(() => {
            this.checkNow();
        }, this.interval);
    }

    // Stop monitoring
    stop() {
        this.isRunning = false;
        if (this.timerId) {
            clearInterval(this.timerId);
            this.timerId = null;
        }
    }

    // Check health now
    async checkNow() {
        try {
            const result = await this.checkHealth();
            this.lastResult = result;
            this.processResult(result);
            return result;
        } catch (error) {
            console.error('Health check failed:', error);
            this.processResult({ status: 'unhealthy', error: error.message });
            return { status: 'unhealthy', error: error.message };
        }
    }

    // Check health endpoint
    async checkHealth() {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), this.timeout);

        try {
            const response = await fetch(this.healthEndpoint, {
                signal: controller.signal,
                headers: {
                    'Accept': 'application/json',
                },
            });

            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            return this.parseHealthResponse(data);
        } catch (error) {
            clearTimeout(timeoutId);
            if (error.name === 'AbortError') {
                throw new Error('Health check timeout');
            }
            throw error;
        }
    }

    // Parse health response
    parseHealthResponse(data) {
        // Check if response has success flag
        if (data.success === true) {
            return { status: 'healthy', data: data.data };
        }

        // Check for health_score
        if (data.health_score !== undefined) {
            const score = data.health_score;
            if (score >= 80) {
                return { status: 'healthy', data: data };
            } else if (score >= 50) {
                return { status: 'degraded', data: data };
            } else {
                return { status: 'unhealthy', data: data };
            }
        }

        // Check for status field
        if (data.status === 'healthy' || data.status === 'ok') {
            return { status: 'healthy', data: data };
        }

        if (data.status === 'degraded') {
            return { status: 'degraded', data: data };
        }

        // Default: assume healthy if we got a response
        return { status: 'healthy', data: data };
    }

    // Process health result
    processResult(result) {
        if (result.status === 'healthy') {
            this.onHealthy(result);
        } else if (result.status === 'degraded') {
            this.onDegraded(result);
        } else {
            this.onUnhealthy(result);
        }
    }

    // Get last result
    getLastResult() {
        return this.lastResult;
    }

    // Check if backend is healthy
    isHealthy() {
        return this.lastResult && this.lastResult.status === 'healthy';
    }
}

export default HealthMonitor;
