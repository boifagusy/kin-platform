import { registerPlugin } from '@capacitor/core';
import type { KinDevicePlugin } from './definitions';

const KinDevice = registerPlugin<KinDevicePlugin>('KinDevice', {
  web: () => import('./web').then((m) => new m.KinDeviceWeb()),
});

export * from './definitions';
export { KinDevice };
