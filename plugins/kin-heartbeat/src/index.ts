import { registerPlugin } from '@capacitor/core';
import type { KinHeartbeatPlugin } from './definitions';

const KinHeartbeat = registerPlugin<KinHeartbeatPlugin>('KinHeartbeat', {
  web: () => import('./web').then((m) => new m.KinHeartbeatWeb()),
});

export * from './definitions';
export { KinHeartbeat };
