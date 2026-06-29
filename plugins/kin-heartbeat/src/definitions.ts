export interface HeartbeatStatus {
  status: 'active' | 'idle' | 'inactive';
  lastHeartbeat: string | null;
  interval: number;
  isRunning: boolean;
}

export interface HeartbeatData {
  timestamp: string;
  status: string;
  location?: {
    latitude: number;
    longitude: number;
  };
  battery?: number;
  network?: string;
}

export interface KinHeartbeatPlugin {
  start(interval: number): Promise<{ success: boolean }>;
  stop(): Promise<{ success: boolean }>;
  getStatus(): Promise<HeartbeatStatus>;
  getLastHeartbeat(): Promise<HeartbeatData | null>;
  setStatus(status: 'active' | 'idle' | 'inactive'): Promise<{ success: boolean }>;
  onHeartbeat(callback: (data: HeartbeatData) => void): void;
}
