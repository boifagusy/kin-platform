// KIN Platform — OfflineManager
// Manages offline detection, request queuing, and replay

import EventBus, { Events } from '../events/EventBus';
import configService from '../config/ConfigService';
import connectionMetrics from '../metrics/ConnectionMetrics';

class OfflineManager {
    constructor(config = {}) {
        this.queue = [];
        this.isOffline = false;
        this.isReplaying = false;
        this.maxQueueSize = config.maxQueueSize || 100;
        this.ttl = config.ttl || 10 * 60 * 1000; // 10 minutes
        this.eventBus = EventBus;
        this.metrics = connectionMetrics;
        this.listeners = [];
        this.setupListeners();
    }

    setupListeners() {
        // Browser online/offline events
        if (typeof window !== 'undefined') {
            const onOnline = () => this.handleOnline();
            const onOffline = () => this.handleOffline();
            window.addEventListener('online', onOnline);
            window.addEventListener('offline', onOffline);
            this.listeners.push({ event: 'online', handler: onOnline });
            this.listeners.push({ event: 'offline', handler: onOffline });
        }

        // Initial state
        this.isOffline = typeof navigator !== 'undefined' ? !navigator.onLine : false;
        if (this.isOffline) {
            this.eventBus.publish(Events.CONNECTION_OFFLINE, {
                timestamp: Date.now(),
                reason: 'initial_state'
            });
        }
    }

    // Queue a request
    enqueue(request) {
        // Clean expired items first
        this.cleanExpired();

        // Check queue size
        if (this.queue.length >= this.maxQueueSize) {
            this.removeOldest();
        }

        const queuedItem = {
            id: this.generateId(),
            request: {
                url: request.url,
                options: request.options || {},
                method: request.method || 'GET',
                data: request.data || null,
            },
            timestamp: Date.now(),
            expiresAt: Date.now() + this.ttl,
            attempts: 0,
            maxAttempts: request.maxAttempts || 3,
        };

        this.queue.push(queuedItem);
        this.eventBus.publish('queue.item.added', {
            id: queuedItem.id,
            queueSize: this.queue.length,
            timestamp: Date.now()
        });

        return queuedItem.id;
    }

    // Replay all queued requests
    async replay(client) {
        if (this.isReplaying || this.isOffline) {
            return;
        }

        if (this.queue.length === 0) {
            return;
        }

        this.isReplaying = true;
        this.eventBus.publish('queue.replay.started', {
            queueSize: this.queue.length,
            timestamp: Date.now()
        });

        let successCount = 0;
        let failureCount = 0;

        // Sort by priority and timestamp (FIFO)
        const sortedQueue = [...this.queue].sort((a, b) => a.timestamp - b.timestamp);

        for (const item of sortedQueue) {
            // Check if expired
            if (Date.now() > item.expiresAt) {
                this.removeItem(item.id);
                continue;
            }

            // Check if exceeded max attempts
            if (item.attempts >= item.maxAttempts) {
                this.removeItem(item.id);
                this.eventBus.publish('queue.replay.failed', {
                    id: item.id,
                    reason: 'max_attempts_exceeded',
                    attempts: item.attempts,
                    timestamp: Date.now()
                });
                continue;
            }

            try {
                const result = await this.executeRequest(client, item);
                this.removeItem(item.id);
                successCount++;
                this.eventBus.publish('queue.replay.success', {
                    id: item.id,
                    url: item.request.url,
                    timestamp: Date.now()
                });
            } catch (error) {
                item.attempts++;
                failureCount++;
                this.eventBus.publish('queue.replay.failed', {
                    id: item.id,
                    url: item.request.url,
                    error: error.message,
                    attempts: item.attempts,
                    timestamp: Date.now()
                });

                // If still has attempts, keep in queue
                if (item.attempts < item.maxAttempts) {
                    // Keep in queue for next replay
                } else {
                    this.removeItem(item.id);
                }
            }
        }

        this.isReplaying = false;
        this.eventBus.publish('queue.replay.completed', {
            successCount,
            failureCount,
            remainingQueue: this.queue.length,
            timestamp: Date.now()
        });
    }

    // Execute a single request
    async executeRequest(client, item) {
        const { url, options, method, data } = item.request;

        switch (method.toUpperCase()) {
            case 'GET':
                return await client.get(url, options);
            case 'POST':
                return await client.post(url, data, options);
            case 'PUT':
                return await client.put(url, data, options);
            case 'PATCH':
                return await client.patch(url, data, options);
            case 'DELETE':
                return await client.delete(url, options);
            default:
                return await client.request(url, { ...options, method });
        }
    }

    // Handle online event
    handleOnline() {
        if (this.isOffline) {
            this.isOffline = false;
            this.eventBus.publish(Events.CONNECTION_ONLINE, {
                timestamp: Date.now(),
                queueSize: this.queue.length
            });
            this.metrics.recordReconnect();
        }
    }

    // Handle offline event
    handleOffline() {
        if (!this.isOffline) {
            this.isOffline = true;
            this.eventBus.publish(Events.CONNECTION_OFFLINE, {
                timestamp: Date.now(),
                queueSize: this.queue.length
            });
            this.metrics.recordFailure(new Error('Device went offline'));
        }
    }

    // Clean expired items
    cleanExpired() {
        const now = Date.now();
        const initialSize = this.queue.length;
        this.queue = this.queue.filter(item => item.expiresAt > now);
        const removed = initialSize - this.queue.length;
        if (removed > 0) {
            this.eventBus.publish('queue.cleaned', {
                removedCount: removed,
                remaining: this.queue.length,
                timestamp: Date.now()
            });
        }
    }

    // Remove oldest item
    removeOldest() {
        const oldest = this.queue.shift();
        if (oldest) {
            this.eventBus.publish('queue.item.removed', {
                id: oldest.id,
                reason: 'queue_full',
                timestamp: Date.now()
            });
        }
    }

    // Remove specific item
    removeItem(id) {
        const index = this.queue.findIndex(item => item.id === id);
        if (index !== -1) {
            this.queue.splice(index, 1);
            this.eventBus.publish('queue.item.removed', {
                id: id,
                reason: 'processed',
                timestamp: Date.now()
            });
        }
    }

    // Get queue status
    getStatus() {
        return {
            isOffline: this.isOffline,
            isReplaying: this.isReplaying,
            queueSize: this.queue.length,
            maxQueueSize: this.maxQueueSize,
            items: this.queue.map(item => ({
                id: item.id,
                url: item.request.url,
                method: item.request.method,
                attempts: item.attempts,
                maxAttempts: item.maxAttempts,
                age: (Date.now() - item.timestamp) / 1000,
                expiresIn: (item.expiresAt - Date.now()) / 1000,
            }))
        };
    }

    // Clear queue
    clearQueue() {
        const size = this.queue.length;
        this.queue = [];
        this.eventBus.publish('queue.cleared', {
            clearedCount: size,
            timestamp: Date.now()
        });
        return size;
    }

    // Generate unique ID
    generateId() {
        return Date.now().toString(36) + Math.random().toString(36).substring(2, 9);
    }

    // Clean up listeners
    destroy() {
        if (typeof window !== 'undefined') {
            for (const listener of this.listeners) {
                window.removeEventListener(listener.event, listener.handler);
            }
        }
        this.listeners = [];
        this.queue = [];
        this.isOffline = false;
        this.isReplaying = false;
    }
}

export default OfflineManager;
