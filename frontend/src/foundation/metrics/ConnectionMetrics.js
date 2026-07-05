// KIN Platform — ConnectionMetrics
// Track connection performance metrics

class ConnectionMetrics {
    constructor() {
        this.metrics = {
            latency: [],
            reconnectCount: 0,
            failureCount: 0,
            successCount: 0,
            totalRequests: 0,
            averageResponseTime: 0,
            healthScore: 100,
            lastSuccessfulRequest: null,
            lastFailedRequest: null,
            lastReconnect: null,
            uptime: {
                start: Date.now(),
                total: 0,
                connected: 0,
                disconnected: 0,
            },
            history: [],
        };

        this.maxHistorySize = 100;
        this.lastStatus = 'unknown';
        this.statusStartTime = Date.now();
    }

    // Record a successful request
    recordSuccess(duration) {
        this.metrics.successCount++;
        this.metrics.totalRequests++;
        this.metrics.lastSuccessfulRequest = Date.now();

        // Record latency
        if (duration !== undefined) {
            this.metrics.latency.push(duration);
            if (this.metrics.latency.length > 100) {
                this.metrics.latency.shift();
            }
            this.metrics.averageResponseTime = this.calculateAverage(this.metrics.latency);
        }

        this.updateUptime('connected');
        this.updateHealthScore(1);
        this.addHistory('success', { duration });
    }

    // Record a failure
    recordFailure(error) {
        this.metrics.failureCount++;
        this.metrics.totalRequests++;
        this.metrics.lastFailedRequest = Date.now();

        this.updateUptime('disconnected');
        this.updateHealthScore(-1);
        this.addHistory('failure', { error: error.message || 'Unknown error' });
    }

    // Record a reconnect
    recordReconnect() {
        this.metrics.reconnectCount++;
        this.metrics.lastReconnect = Date.now();
        this.addHistory('reconnect', { count: this.metrics.reconnectCount });
    }

    // Record latency
    recordLatency(duration) {
        this.metrics.latency.push(duration);
        if (this.metrics.latency.length > 100) {
            this.metrics.latency.shift();
        }
        this.metrics.averageResponseTime = this.calculateAverage(this.metrics.latency);
    }

    // Update health score
    updateHealthScore(delta) {
        this.metrics.healthScore = Math.max(0, Math.min(100, this.metrics.healthScore + delta));
        if (delta > 0) {
            this.metrics.healthScore = Math.min(100, this.metrics.healthScore + 5);
        } else {
            this.metrics.healthScore = Math.max(0, this.metrics.healthScore - 10);
        }
    }

    // Update uptime tracking
    updateUptime(status) {
        const now = Date.now();
        const duration = (now - this.statusStartTime) / 1000;

        if (status === 'connected') {
            this.metrics.uptime.total += duration;
            this.metrics.uptime.connected += duration;
        } else {
            this.metrics.uptime.total += duration;
            this.metrics.uptime.disconnected += duration;
        }

        this.statusStartTime = now;
        this.lastStatus = status;
    }

    // Calculate average
    calculateAverage(arr) {
        if (arr.length === 0) return 0;
        const sum = arr.reduce((a, b) => a + b, 0);
        return sum / arr.length;
    }

    // Add to history
    addHistory(type, data) {
        this.metrics.history.push({
            type,
            data,
            timestamp: Date.now(),
        });

        if (this.metrics.history.length > this.maxHistorySize) {
            this.metrics.history.shift();
        }
    }

    // Get metrics snapshot
    getMetrics() {
        const now = Date.now();
        const uptimeTotal = (now - this.metrics.uptime.start) / 1000;

        return {
            ...this.metrics,
            uptime: {
                ...this.metrics.uptime,
                total: uptimeTotal,
                connected: this.metrics.uptime.connected + (this.lastStatus === 'connected' ? (now - this.statusStartTime) / 1000 : 0),
                disconnected: this.metrics.uptime.disconnected + (this.lastStatus === 'disconnected' ? (now - this.statusStartTime) / 1000 : 0),
            },
            healthScore: this.metrics.healthScore,
            isHealthy: this.metrics.healthScore >= 80,
            isDegraded: this.metrics.healthScore >= 50 && this.metrics.healthScore < 80,
            isUnhealthy: this.metrics.healthScore < 50,
            lastLatency: this.metrics.latency.length > 0 ? this.metrics.latency[this.metrics.latency.length - 1] : null,
            averageLatency: this.metrics.averageResponseTime,
            successRate: this.metrics.totalRequests > 0 ? this.metrics.successCount / this.metrics.totalRequests : 0,
        };
    }

    // Reset metrics
    reset() {
        this.metrics = {
            latency: [],
            reconnectCount: 0,
            failureCount: 0,
            successCount: 0,
            totalRequests: 0,
            averageResponseTime: 0,
            healthScore: 100,
            lastSuccessfulRequest: null,
            lastFailedRequest: null,
            lastReconnect: null,
            uptime: {
                start: Date.now(),
                total: 0,
                connected: 0,
                disconnected: 0,
            },
            history: [],
        };
        this.lastStatus = 'unknown';
        this.statusStartTime = Date.now();
    }

    // Get health status label
    getHealthLabel() {
        const score = this.metrics.healthScore;
        if (score >= 80) return 'healthy';
        if (score >= 50) return 'degraded';
        return 'unhealthy';
    }

    // Get health status emoji
    getHealthEmoji() {
        const label = this.getHealthLabel();
        if (label === 'healthy') return '🟢';
        if (label === 'degraded') return '🟡';
        return '🔴';
    }
}

// Singleton instance
const connectionMetrics = new ConnectionMetrics();
export default connectionMetrics;
