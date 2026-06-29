export interface Notification {
  id: string;
  title: string;
  body: string;
  data?: Record<string, any>;
  scheduleAt?: string; // ISO timestamp
  recurring?: boolean;
  recurringInterval?: 'daily' | 'weekly' | 'monthly';
  sound?: boolean;
  vibrate?: boolean;
  priority?: 'high' | 'normal' | 'low';
}

export interface NotificationAction {
  id: string;
  title: string;
  action: 'open' | 'dismiss' | 'custom';
  data?: Record<string, any>;
}

export interface KinNotificationsPlugin {
  schedule(notification: Notification): Promise<{ id: string }>;
  cancel(notificationId: string): Promise<{ success: boolean }>;
  cancelAll(): Promise<{ success: boolean }>;
  getScheduled(): Promise<{ notifications: Notification[] }>;
  checkPermissions(): Promise<{ granted: boolean }>;
  requestPermissions(): Promise<{ granted: boolean }>;
  onNotificationClick(callback: (notification: Notification) => void): void;
  onNotificationAction(callback: (action: NotificationAction) => void): void;
}
