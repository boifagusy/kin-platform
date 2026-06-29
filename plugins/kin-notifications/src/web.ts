import { WebPlugin } from '@capacitor/core';
import type { KinNotificationsPlugin, Notification, NotificationAction } from './definitions';

export class KinNotificationsWeb extends WebPlugin implements KinNotificationsPlugin {
  private notifications: Map<string, Notification> = new Map();
  private scheduledTimers: Map<string, number> = new Map();
  private clickListeners: ((notification: Notification) => void)[] = [];
  private actionListeners: ((action: NotificationAction) => void)[] = [];

  async schedule(notification: Notification): Promise<{ id: string }> {
    const id = notification.id || `notif_${Date.now()}`;
    this.notifications.set(id, notification);

    if (notification.scheduleAt) {
      const scheduleTime = new Date(notification.scheduleAt).getTime();
      const now = Date.now();
      const delay = Math.max(0, scheduleTime - now);

      const timerId = setTimeout(() => {
        this.showNotification(notification);
      }, delay);

      this.scheduledTimers.set(id, timerId);
    } else {
      this.showNotification(notification);
    }

    return { id };
  }

  async cancel(notificationId: string): Promise<{ success: boolean }> {
    if (this.scheduledTimers.has(notificationId)) {
      clearTimeout(this.scheduledTimers.get(notificationId));
      this.scheduledTimers.delete(notificationId);
    }
    this.notifications.delete(notificationId);
    return { success: true };
  }

  async cancelAll(): Promise<{ success: boolean }> {
    for (const [id, timerId] of this.scheduledTimers) {
      clearTimeout(timerId);
    }
    this.scheduledTimers.clear();
    this.notifications.clear();
    return { success: true };
  }

  async getScheduled(): Promise<{ notifications: Notification[] }> {
    return { notifications: Array.from(this.notifications.values()) };
  }

  async checkPermissions(): Promise<{ granted: boolean }> {
    if (!('Notification' in window)) {
      return { granted: false };
    }
    return { granted: Notification.permission === 'granted' };
  }

  async requestPermissions(): Promise<{ granted: boolean }> {
    if (!('Notification' in window)) {
      return { granted: false };
    }
    const result = await Notification.requestPermission();
    return { granted: result === 'granted' };
  }

  onNotificationClick(callback: (notification: Notification) => void): void {
    this.clickListeners.push(callback);
  }

  onNotificationAction(callback: (action: NotificationAction) => void): void {
    this.actionListeners.push(callback);
  }

  private showNotification(notification: Notification): void {
    if (!('Notification' in window) || Notification.permission !== 'granted') {
      console.warn('Notifications not available:', notification);
      return;
    }

    const options: NotificationOptions = {
      body: notification.body,
      data: notification.data,
      tag: notification.id,
      requireInteraction: true,
    };

    if (notification.sound) {
      options.silent = false;
    }
    if (notification.vibrate) {
      options.vibrate = [100, 50, 100];
    }

    const webNotification = new Notification(notification.title, options);

    webNotification.onclick = () => {
      for (const listener of this.clickListeners) {
        listener(notification);
      }
    };

    webNotification.onclose = () => {
      // Handle close if needed
    };
  }
}
