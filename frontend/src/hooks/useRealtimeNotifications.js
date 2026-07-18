import { useEffect, useRef, useCallback } from 'react';
import echo from '../services/echo';
import notificationFeedService from '../services/notificationFeedService';

export default function useRealtimeNotifications({
    userId,
    onNewNotification,
    onBadgeUpdate,
    onToast,
    enabled = true,
}) {
    const dedupeSet = useRef(new Set());
    const pollingInterval = useRef(null);

    const catchUp = useCallback(async () => {
        try {
            const count = await notificationFeedService.getUnreadCount();
            if (onBadgeUpdate) onBadgeUpdate(count);
        } catch (e) {
            console.warn('[N3] Catch-up fetch failed:', e);
        }
    }, [onBadgeUpdate]);

    const startPolling = useCallback(() => {
        if (pollingInterval.current) return;
        pollingInterval.current = setInterval(() => catchUp(), 30000);
    }, [catchUp]);

    const stopPolling = useCallback(() => {
        if (pollingInterval.current) {
            clearInterval(pollingInterval.current);
            pollingInterval.current = null;
        }
    }, []);

    useEffect(() => {
        if (!enabled || !userId) return;

        const channel = echo.private(`notifications.${userId}`);

        channel.listen('.NotificationDispatched', (event) => {
            if (dedupeSet.current.has(event.notification_id)) return;
            dedupeSet.current.add(event.notification_id);

            if (dedupeSet.current.size > 1000) {
                const entries = [...dedupeSet.current];
                dedupeSet.current = new Set(entries.slice(-500));
            }

            if (onNewNotification) onNewNotification(event);
            if (onBadgeUpdate) onBadgeUpdate(event.badge_count);
            if (onToast) {
                onToast({
                    title: event.title,
                    body: event.body,
                    type: event.type === 'emergency' ? 'error' : 'info',
                });
            }
        });

        echo.connector.socket.on('connected', () => { stopPolling(); catchUp(); });
        echo.connector.socket.on('disconnected', () => startPolling());

        startPolling();

        return () => { channel.unsubscribe(); stopPolling(); };
    }, [userId, enabled, onNewNotification, onBadgeUpdate, onToast, catchUp, startPolling, stopPolling]);

    return { catchUp };
}
