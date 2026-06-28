import { registerPlugin } from '@capacitor/core';

import type { KinLocationPlugin } from './definitions';

const KinLocation = registerPlugin<KinLocationPlugin>('KinLocation', {
  web: () => import('./web').then((m) => new m.KinLocationWeb()),
});

export * from './definitions';
export { KinLocation };
