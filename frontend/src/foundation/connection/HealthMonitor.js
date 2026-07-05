// KIN Platform — HealthMonitor
// Polls backend health endpoint using FetchClient

import FetchClient from './FetchClient';
import configService from '../config/ConfigService';
import EventBus, { Events } from '../events/EventBus';
import connectionMetrics from '../metrics/ConnectionMetrics';

class HealthMonitor {
    constructor(config = {}) {
        const healthConfig = configService.getHealthConfig();
        this.interval = config.interval || healthConfig.interval;
        this.healthEndpoint = config.healthEndpoint || healthConfig.endpoint;
        this.timeout = config.timeout || healthConfig.timeout;
        this.onHealthy = config.onHealthy || (() => {});
        this.onDegraded = config.onDegraded || (() => {});
        this.onUnhealthy = config.onUnhealthy || (() => {});
        this.isRunning = false;
        this.timerId = null;
        this.lastResult = null;
        this.client = FetchClient;
        this.eventBus = EventBus;
        this.metrics = connectionMetrics;
    }

    async start() {
        if (this.isRunning) {
            return;
        }

        this.isRunning = true;
        await this.checkNow();

        this.timerId = setInterval(() => {
            this.checkNow();
        }, this.interval);
    }

    stop() {
        this.isRunning = false;
        if (this.timerId) {
            clearInterval(this.timerId);
            this.timerId = null;
        }
    }

    async checkNow() {
        this.eventBus.publish(Events.HEALTH_CHECK, { timestamp: Date.now() });

        try {
            const result = await this.client.get(this.healthEndpoint);
            const parsed = this.parseHealthResponse(result);
            this.lastResult = parsed;
            this.processResult(parsed);
            this.eventBus.publish(Events.HEALTH_RESPONSE, { result: parsed, timestamp: Date.now() });
            return parsed;
        } catch (error) {
            console.error('Health check failed:', error);
            const result = { status: 'unhealthy', error: error.message };
            this.lastResult = result;
            this.processResult(result);
            this.eventBus.publish(Events.HEALTH_RESPONSE, { result, error, timestamp: Date.now() });
            this.metrics.recordFailure(error);
            return result;
        }
    }

    parseHealthResponse(data) {
        if (data.success === true) {
            return { status: 'healthy', data: data.data };
        }

        if (data.health_score !== undefined) {
            const score = data.health_score;
            if (score >= 80) return { status: 'healthy', data: data };
            if (score >= 50) return { status: 'degraded', data: data };
            return { status: 'unhealthy', data: data };
        }

        if (data.status === 'healthy' || data.status === 'ok') {
            return { status: 'healthy', data: data };
        }

        if (data.status === 'degraded') {
            return { status: 'degraded', data: data };
        }

        return { status: 'healthy', data: data };
    }

    processResult(result) {
        if (result.status === 'healthy') {
            this.onHealthy(result);
        } else if (result.status === 'degraded') {
            this.onDegraded(result);
        } else {
            this.onUnhealthy(result);
        }
    }

    getLastResult() {
        return this.lastResult;
    }

    isHealthy() {
        return this.lastResult && this.lastResult.status === 'healthy';
    }
}

export default HealthMonitor;
