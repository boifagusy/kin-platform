// KIN Platform — EventBus
// Central event communication for all subsystems

class EventBus {
    constructor() {
        this.subscribers = new Map();
        this.eventLog = [];
        this.maxLogSize = 100;
    }

    // Subscribe to an event
    subscribe(event, callback, context = null) {
        if (!this.subscribers.has(event)) {
            this.subscribers.set(event, []);
        }

        const subscriber = { callback, context };
        this.subscribers.get(event).push(subscriber);

        // Return unsubscribe function
        return () => {
            this.unsubscribe(event, callback);
        };
    }

    // Unsubscribe from an event
    unsubscribe(event, callback) {
        if (!this.subscribers.has(event)) {
            return;
        }

        const subscribers = this.subscribers.get(event);
        const index = subscribers.findIndex(s => s.callback === callback);
        if (index !== -1) {
            subscribers.splice(index, 1);
        }

        // Clean up empty event
        if (subscribers.length === 0) {
            this.subscribers.delete(event);
        }
    }

    // Publish an event
    publish(event, payload = {}) {
        // Log event
        this.logEvent(event, payload);

        // Dispatch to subscribers
        if (!this.subscribers.has(event)) {
            return;
        }

        const subscribers = this.subscribers.get(event);
        const timestamp = Date.now();

        for (const subscriber of subscribers) {
            try {
                if (subscriber.context) {
                    subscriber.callback.call(subscriber.context, payload, { event, timestamp });
                } else {
                    subscriber.callback(payload, { event, timestamp });
                }
            } catch (error) {
                console.error(`EventBus error in event "${event}":`, error);
            }
        }
    }

    // Log event
    logEvent(event, payload) {
        this.eventLog.push({
            event,
            payload,
            timestamp: Date.now(),
            id: crypto.randomUUID ? crypto.randomUUID() : Math.random().toString(36).substring(2),
        });

        // Trim log
        if (this.eventLog.length > this.maxLogSize) {
            this.eventLog.shift();
        }
    }

    // Get event log
    getEventLog(filter = null) {
        if (filter) {
            return this.eventLog.filter(entry => entry.event === filter);
        }
        return [...this.eventLog];
    }

    // Clear event log
    clearLog() {
        this.eventLog = [];
    }

    // Check if event has subscribers
    hasSubscribers(event) {
        return this.subscribers.has(event) && this.subscribers.get(event).length > 0;
    }

    // Get subscriber count
    getSubscriberCount(event = null) {
        if (event) {
            return this.subscribers.has(event) ? this.subscribers.get(event).length : 0;
        }

        let total = 0;
        for (const subscribers of this.subscribers.values()) {
            total += subscribers.length;
        }
        return total;
    }

    // Get all event names
    getEventNames() {
        return Array.from(this.subscribers.keys());
    }

    // Clear all subscribers
    clearAll() {
        this.subscribers.clear();
    }
}

// Event constants
export const Events = {
    // Connection events
    CONNECTION_ONLINE: 'connection.online',
    CONNECTION_OFFLINE: 'connection.offline',
    CONNECTION_CONNECTED: 'connection.connected',
    CONNECTION_DEGRADED: 'connection.degraded',
    CONNECTION_ERROR: 'connection.error',
    CONNECTION_RETRY: 'connection.retry',
    CONNECTION_LATENCY: 'connection.latency',

    // Health events
    HEALTH_HEALTHY: 'health.healthy',
    HEALTH_DEGRADED: 'health.degraded',
    HEALTH_UNHEALTHY: 'health.unhealthy',
    HEALTH_CHECK: 'health.check',
    HEALTH_RESPONSE: 'health.response',

    // Metrics events
    METRICS_UPDATE: 'metrics.update',
    METRICS_FAILURE: 'metrics.failure',
    METRICS_RECONNECT: 'metrics.reconnect',

    // System events
    SYSTEM_BOOT: 'system.boot',
    SYSTEM_SHUTDOWN: 'system.shutdown',
    SYSTEM_ERROR: 'system.error',
};

// Singleton instance
const eventBus = new EventBus();
export default eventBus;
