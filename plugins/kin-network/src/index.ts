import { registerPlugin } from '@capacitor/core';

import type { KinNetworkPlugin } from './definitions';

const KinNetwork = registerPlugin<KinNetworkPlugin>('KinNetwork', {
  web: () => import('./web').then((m) => new m.KinNetworkWeb()),
});

export * from './definitions';
export { KinNetwork };
