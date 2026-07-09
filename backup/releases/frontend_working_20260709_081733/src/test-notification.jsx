import { LocalNotifications } from '@capacitor/local-notifications';

export const TestNotification = () => {
  const scheduleNotification = async () => {
    await LocalNotifications.schedule({
      notifications: [
        {
          title: "🔔 KIN Check-In Reminder",
          body: "Don't forget to check in!",
          id: 1,
          schedule: { at: new Date(Date.now() + 5000) }, // 5 seconds from now
          sound: true,
          vibrate: true,
          actionTypeId: "checkin",
          extra: { screen: "/settings/check-in" }
        }
      ]
    });
  };

  return (
    <button onClick={scheduleNotification}>
      Test Notification (5 seconds)
    </button>
  );
};
