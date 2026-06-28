import { registerPlugin } from '@capacitor/core';

import type { KinCorePlugin } from './definitions';

const KinCore = registerPlugin<KinCorePlugin>('KinCore', {
  web: () => import('./web').then((m) => new m.KinCoreWeb()),
});

export * from './definitions';
export { KinCore };
