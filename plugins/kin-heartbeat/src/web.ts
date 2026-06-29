import { WebPlugin } from '@capacitor/core';
import type { KinHeartbeatPlugin, HeartbeatStatus, HeartbeatData } from './definitions';

export class KinHeartbeatWeb extends WebPlugin implements KinHeartbeatPlugin {
  private isRunning = false;
  private intervalId: number | null = null;
  private currentStatus: HeartbeatStatus = {
    status: 'idle',
    lastHeartbeat: null,
    interval: 60000,
    isRunning: false,
  };
  private lastHeartbeatData: HeartbeatData | null = null;
  private heartbeatListeners: ((data: HeartbeatData) => void)[] = [];

  async start(interval: number): Promise<{ success: boolean }> {
    if (this.isRunning) {
      return { success: true };
    }

    this.isRunning = true;
    this.currentStatus.isRunning = true;
    this.currentStatus.interval = interval;

    // Send initial heartbeat
    this.sendHeartbeat();

    this.intervalId = setInterval(() => {
      this.sendHeartbeat();
    }, interval);

    return { success: true };
  }

  async stop(): Promise<{ success: boolean }> {
    if (this.intervalId) {
      clearInterval(this.intervalId);
      this.intervalId = null;
    }
    this.isRunning = false;
    this.currentStatus.isRunning = false;
    this.currentStatus.status = 'idle';
    return { success: true };
  }

  async getStatus(): Promise<HeartbeatStatus> {
    return this.currentStatus;
  }

  async getLastHeartbeat(): Promise<HeartbeatData | null> {
    return this.lastHeartbeatData;
  }

  async setStatus(status: 'active' | 'idle' | 'inactive'): Promise<{ success: boolean }> {
    this.currentStatus.status = status;
    this.sendHeartbeat();
    return { success: true };
  }

  onHeartbeat(callback: (data: HeartbeatData) => void): void {
    this.heartbeatListeners.push(callback);
  }

  private async sendHeartbeat(): Promise<void> {
    const now = new Date().toISOString();
    const data: HeartbeatData = {
      timestamp: now,
      status: this.currentStatus.status,
    };

    // Try to get location if available
    try {
      if ('geolocation' in navigator) {
        const position = await new Promise<GeolocationPosition>((resolve, reject) => {
          navigator.geolocation.getCurrentPosition(resolve, reject, {
            timeout: 5000,
            maximumAge: 60000,
          });
        });
        data.location = {
          latitude: position.coords.latitude,
          longitude: position.coords.longitude,
        };
      }
    } catch {
      // Location not available, skip
    }

    // Try to get battery info
    try {
      const battery = await (navigator as any).getBattery();
      data.battery = Math.round(battery.level * 100);
    } catch {
      // Battery not available, skip
    }

    data.network = navigator.onLine ? 'online' : 'offline';

    this.lastHeartbeatData = data;
    this.currentStatus.lastHeartbeat = now;

    // Notify listeners
    for (const listener of this.heartbeatListeners) {
      try {
        listener(data);
      } catch (e) {
        console.error('Heartbeat listener error:', e);
      }
    }

    console.log('💓 Heartbeat sent:', data);
  }
}
