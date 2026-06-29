export interface DeviceInfo {
  platform: 'android' | 'ios' | 'web';
  model: string;
  osVersion: string;
  appVersion: string;
  buildNumber: string;
  isNative: boolean;
}

export interface KinCorePlugin {
  getAppVersion(): Promise<{ version: string; build: string }>;
  getDeviceInfo(): Promise<DeviceInfo>;
  getPlatform(): Promise<{ platform: 'android' | 'ios' | 'web' }>;
  isNative(): Promise<{ isNative: boolean }>;
  getBuildNumber(): Promise<{ buildNumber: string }>;
}
