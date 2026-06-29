export interface BatteryInfo {
  level: number; // 0-100
  isCharging: boolean;
  chargingType?: 'ac' | 'usb' | 'wireless' | 'unknown';
  health?: 'good' | 'overheat' | 'dead' | 'unknown';
  temperature?: number;
}

export interface NetworkInfo {
  type: 'wifi' | 'cellular' | 'ethernet' | 'bluetooth' | 'unknown' | 'none';
  connected: boolean;
  carrier?: string;
  signalStrength?: number;
  ipAddress?: string;
  ssid?: string;
}

export interface DeviceInfo {
  model: string;
  manufacturer: string;
  osVersion: string;
  osName: string;
  isEmulator: boolean;
  totalMemory?: number;
  freeMemory?: number;
}

export interface KinDevicePlugin {
  getBatteryInfo(): Promise<BatteryInfo>;
  getNetworkInfo(): Promise<NetworkInfo>;
  getDeviceInfo(): Promise<DeviceInfo>;
  isCharging(): Promise<{ isCharging: boolean }>;
  getCarrier(): Promise<{ carrier: string | null }>;
}
