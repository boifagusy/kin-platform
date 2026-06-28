import { WebPlugin } from '@capacitor/core';

import type { KinSecurityPlugin } from './definitions';

export class KinSecurityWeb extends WebPlugin implements KinSecurityPlugin {
  async echo(options: { value: string }): Promise<{ value: string }> {
    console.log('ECHO', options);
    return options;
  }
}
