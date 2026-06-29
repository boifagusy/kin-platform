import { registerPlugin } from '@capacitor/core';

import type { KinNotificationsPlugin } from './definitions';

const KinNotifications = registerPlugin<KinNotificationsPlugin>('KinNotifications', {
  web: () => import('./web').then((m) => new m.KinNotificationsWeb()),
});

export * from './definitions';
export { KinNotifications };
