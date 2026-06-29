import { WebPlugin } from '@capacitor/core';

import type { KinDevicePlugin } from './definitions';

export class KinDeviceWeb extends WebPlugin implements KinDevicePlugin {
  async echo(options: { value: string }): Promise<{ value: string }> {
    console.log('ECHO', options);
    return options;
  }
}
