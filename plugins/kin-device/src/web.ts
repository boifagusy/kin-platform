import { WebPlugin } from '@capacitor/core';
import type { KinDevicePlugin, BatteryInfo, NetworkInfo, DeviceInfo } from './definitions';

export class KinDeviceWeb extends WebPlugin implements KinDevicePlugin {
  private batteryInfo: BatteryInfo = { level: 100, isCharging: true };

  async getBatteryInfo(): Promise<BatteryInfo> {
    try {
      const battery = await (navigator as any).getBattery();
      this.batteryInfo = {
        level: Math.round(battery.level * 100),
        isCharging: battery.charging,
      };
      return this.batteryInfo;
    } catch {
      return this.batteryInfo;
    }
  }

  async getNetworkInfo(): Promise<NetworkInfo> {
    const connection = (navigator as any).connection;
    return {
      type: connection?.type || 'unknown',
      connected: navigator.onLine,
      carrier: null,
      signalStrength: connection?.downlink ? Math.round(connection.downlink) : undefined,
    };
  }

  async getDeviceInfo(): Promise<DeviceInfo> {
    const ua = navigator.userAgent;
    return {
      model: 'Web Browser',
      manufacturer: 'Unknown',
      osVersion: ua,
      osName: 'Web',
      isEmulator: false,
    };
  }

  async isCharging(): Promise<{ isCharging: boolean }> {
    try {
      const battery = await (navigator as any).getBattery();
      return { isCharging: battery.charging };
    } catch {
      return { isCharging: true };
    }
  }

  async getCarrier(): Promise<{ carrier: string | null }> {
    return { carrier: null };
  }
}
