import { WebPlugin } from '@capacitor/core';
import type { KinCorePlugin, DeviceInfo } from './definitions';

export class KinCoreWeb extends WebPlugin implements KinCorePlugin {
  async getAppVersion(): Promise<{ version: string; build: string }> {
    return {
      version: '1.0.0',
      build: '1',
    };
  }

  async getDeviceInfo(): Promise<DeviceInfo> {
    const ua = navigator.userAgent;
    return {
      platform: 'web',
      model: 'Web Browser',
      osVersion: ua,
      appVersion: '1.0.0',
      buildNumber: '1',
      isNative: false,
    };
  }

  async getPlatform(): Promise<{ platform: 'android' | 'ios' | 'web' }> {
    return { platform: 'web' };
  }

  async isNative(): Promise<{ isNative: boolean }> {
    return { isNative: false };
  }

  async getBuildNumber(): Promise<{ buildNumber: string }> {
    return { buildNumber: '1' };
  }
}
