import { registerPlugin } from '@capacitor/core';
import type { KinSecurityPlugin } from './definitions';

const KinSecurity = registerPlugin<KinSecurityPlugin>('KinSecurity', {
  web: () => import('./web').then((m) => new m.KinSecurityWeb()),
});

export * from './definitions';
export { KinSecurity };
