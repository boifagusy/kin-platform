import { WebPlugin } from '@capacitor/core';

import type { KinNotificationsPlugin } from './definitions';

export class KinNotificationsWeb extends WebPlugin implements KinNotificationsPlugin {
  async echo(options: { value: string }): Promise<{ value: string }> {
    console.log('ECHO', options);
    return options;
  }
}
