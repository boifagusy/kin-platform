import { WebPlugin } from '@capacitor/core';

import type { KinHeartbeatPlugin } from './definitions';

export class KinHeartbeatWeb extends WebPlugin implements KinHeartbeatPlugin {
  async echo(options: { value: string }): Promise<{ value: string }> {
    console.log('ECHO', options);
    return options;
  }
}
