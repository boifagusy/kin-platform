import { WebPlugin } from '@capacitor/core';

import type { KinNetworkPlugin } from './definitions';

export class KinNetworkWeb extends WebPlugin implements KinNetworkPlugin {
  async echo(options: { value: string }): Promise<{ value: string }> {
    console.log('ECHO', options);
    return options;
  }
}
