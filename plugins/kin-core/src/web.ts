import { WebPlugin } from '@capacitor/core';

import type { KinCorePlugin } from './definitions';

export class KinCoreWeb extends WebPlugin implements KinCorePlugin {
  async echo(options: { value: string }): Promise<{ value: string }> {
    console.log('ECHO', options);
    return options;
  }
}
