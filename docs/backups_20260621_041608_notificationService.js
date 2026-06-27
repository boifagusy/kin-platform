import { LocalNotifications } from '@capacitor/local-notifications';

// Store checker interval and phone
let checkerInterval = null;
let currentPhone = null;

// Check notification status
export const checkNotificationStatus = async () => {
  try {
    const hasPermission = await LocalNotifications.checkPermissions();
    return hasPermission.display === 'granted';
  } catch (error) {
    console.error('Check permission error:', error);
    return false;
  }
};

// Request notification permission
export const requestNotificationPermission = async () => {
  try {
    const permission = await LocalNotifications.requestPermissions();
    return permission.display === 'granted';
  } catch (error) {
    console.error('Permission error:', error);
    return false;
  }
};

// Start notification checker with phone number
export const startNotificationChecker = async (phone) => {
  try {
    // Store phone for later use
    currentPhone = phone;
    
    // Check permission
    const hasPermission = await checkNotificationStatus();
    if (!hasPermission) {
      const granted = await requestNotificationPermission();
      if (!granted) {
        console.warn('⚠️ Notification permissions not granted');
        return false;
      }
    }
    
    // Get pending notifications
    const pending = await LocalNotifications.getPending();
    console.log(`📬 ${pending.notifications.length} pending notifications for ${phone}`);
    
    // Schedule daily check-in if not already scheduled
    const dailyCheckin = pending.notifications.some(n => n.id === 1);
    if (!dailyCheckin) {
      console.log('📅 Scheduling daily check-in...');
      await scheduleDailyCheckIn();
    }
    
    // Start interval checker (checks every 5 minutes)
    if (checkerInterval) {
      clearInterval(checkerInterval);
    }
    
    checkerInterval = setInterval(async () => {
      try {
        const currentPending = await LocalNotifications.getPending();
        console.log(`📬 [${phone}] ${currentPending.notifications.length} pending notifications`);
      } catch (error) {
        console.error('Check error:', error);
      }
    }, 300000); // 5 minutes
    
    console.log(`✅ Notification checker started for ${phone}`);
    return true;
  } catch (error) {
    console.error('❌ Start checker error:', error);
    return false;
  }
};

// Stop notification checker
export const stopNotificationChecker = async () => {
  try {
    if (checkerInterval) {
      clearInterval(checkerInterval);
      checkerInterval = null;
      console.log('✅ Notification checker stopped');
      return true;
    }
    console.log('ℹ️ No active checker to stop');
    return true;
  } catch (error) {
    console.error('❌ Stop checker error:', error);
    return false;
  }
};

// Schedule daily check-in at 8 PM
export const scheduleDailyCheckIn = async () => {
  try {
    const hasPermission = await requestNotificationPermission();
    if (!hasPermission) {
      console.warn('❌ Notification permission denied');
      return false;
    }

    // Cancel existing check-in notification
    await LocalNotifications.cancel({ id: 1 });

    // Schedule for 8 PM daily
    const now = new Date();
    const target = new Date();
    target.setHours(20, 0, 0, 0); // 8:00 PM

    if (target <= now) {
      target.setDate(target.getDate() + 1);
    }

    await LocalNotifications.schedule({
      notifications: [
        {
          title: "🔔 Daily Check-In Reminder",
          body: "Don't forget to check in with KIN! Your safety matters.",
          id: 1,
          schedule: {
            at: target,
            every: 'day',
            count: 365
          },
          sound: true,
          vibrate: true,
          actionTypeId: "checkin",
          extra: {
            screen: "/settings/check-in",
            type: "daily_checkin",
            phone: currentPhone || "unknown"
          }
        }
      ]
    });

    console.log(`✅ Daily check-in scheduled for 8 PM (${target.toLocaleString()})`);
    return true;
  } catch (error) {
    console.error('❌ Schedule error:', error);
    return false;
  }
};

// Schedule SOS notification (for trusted contacts)
export const scheduleSOSNotification = async (contactName, contactPhone, incidentId) => {
  try {
    const hasPermission = await requestNotificationPermission();
    if (!hasPermission) {
      console.warn('❌ Notification permission denied');
      return false;
    }

    // Schedule immediate notification
    await LocalNotifications.schedule({
      notifications: [
        {
          title: "🚨 SOS Alert!",
          body: `Emergency alert sent to ${contactName} (${contactPhone})`,
          id: Date.now(),
          schedule: { at: new Date(Date.now() + 1000) }, // 1 second from now
          sound: true,
          vibrate: true,
          actionTypeId: "sos",
          extra: {
            type: "sos",
            contact: contactName,
            incidentId: incidentId,
            screen: "/sos"
          }
        }
      ]
    });

    console.log(`✅ SOS notification scheduled for ${contactName}`);
    return true;
  } catch (error) {
    console.error('❌ SOS notification error:', error);
    return false;
  }
};

// Test notification (for debugging)
export const testNotification = async () => {
  try {
    const hasPermission = await requestNotificationPermission();
    if (!hasPermission) {
      console.warn('❌ Notification permission denied');
      return false;
    }

    await LocalNotifications.schedule({
      notifications: [
        {
          title: "🔔 KIN Test Notification",
          body: "This is a test notification from KIN!",
          id: Date.now(),
          schedule: { at: new Date(Date.now() + 5000) },
          sound: true,
          vibrate: true,
          actionTypeId: "test",
          extra: { 
            test: true,
            phone: currentPhone || "unknown"
          }
        }
      ]
    });

    console.log('✅ Test notification scheduled in 5 seconds');
    return true;
  } catch (error) {
    console.error('❌ Test notification error:', error);
    return false;
  }
};

// Cancel all notifications
export const cancelAllNotifications = async () => {
  try {
    await LocalNotifications.cancelAll();
    console.log('✅ All notifications cancelled');
    return true;
  } catch (error) {
    console.error('❌ Cancel error:', error);
    return false;
  }
};

// Get all pending notifications
export const getPendingNotifications = async () => {
  try {
    const pending = await LocalNotifications.getPending();
    return pending.notifications;
  } catch (error) {
    console.error('❌ Get pending error:', error);
    return [];
  }
};

// Handle notification actions
export const handleNotificationAction = (notification) => {
  console.log('📱 Notification action:', notification);
  
  const screen = notification.extra?.screen || '/dashboard';
  const type = notification.extra?.type || 'unknown';
  const phone = notification.extra?.phone || 'unknown';
  
  console.log(`📱 Action: ${type} for ${phone}`);
  
  // Handle different notification types
  switch(type) {
    case 'daily_checkin':
      window.location.href = '/settings/check-in';
      break;
    case 'sos':
      window.location.href = '/sos';
      break;
    case 'test':
      console.log('🔔 Test notification received');
      break;
    default:
      if (screen) {
        window.location.href = screen;
      }
  }
};

// Initialize notifications (call this on app start)
export const initializeNotifications = async (phone) => {
  try {
    console.log(`🔔 Initializing notifications for ${phone || 'unknown'}`);
    return await startNotificationChecker(phone);
  } catch (error) {
    console.error('❌ Initialize error:', error);
    return false;
  }
};

// Export default
export default {
  checkNotificationStatus,
  requestNotificationPermission,
  startNotificationChecker,
  stopNotificationChecker,
  scheduleDailyCheckIn,
  scheduleSOSNotification,
  testNotification,
  cancelAllNotifications,
  getPendingNotifications,
  handleNotificationAction,
  initializeNotifications
};
