import { WebPlugin } from '@capacitor/core';

import type { KinLocationPlugin } from './definitions';

export class KinLocationWeb extends WebPlugin implements KinLocationPlugin {
  async echo(options: { value: string }): Promise<{ value: string }> {
    console.log('ECHO', options);
    return options;
  }
}
