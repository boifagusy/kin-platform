import { Capacitor, registerPlugin } from '@capacitor/core';

export interface KinSafetyPlugin {
  getSafetyStatus(): Promise<SafetyStatus>;
  performCheckIn(options: CheckInOptions): Promise<CheckInResult>;
  queueEmergency(options: EmergencyOptions): Promise<QueueResult>;
  getEmergencySnapshot(): Promise<EmergencySnapshot>;
  isDeviceTrusted(): Promise<DeviceTrustResult>;
  storeSecure(options: SecureStorageOptions): Promise<{ success: boolean }>;
  retrieveSecure(options: SecureStorageOptions): Promise<{ success: boolean; value: string }>;
}

export interface SafetyStatus {
  confidence: number;
  deviceTrust: number;
  fingerprint: string;
  battery: number;
  charging: boolean;
  network: string;
  timestamp: number;
}

export interface CheckInOptions {
  pin: string;
  duressPin?: string;
  isDuress?: boolean;
  location?: { lat: number; lng: number };
}

export interface CheckInResult {
  success: boolean;
  confidence: number;
  isDuress: boolean;
  timestamp: number;
}

export interface EmergencyOptions {
  type: string;
  data?: any;
  location?: { lat: number; lng: number };
}

export interface QueueResult {
  queued: boolean;
  id: string;
  priority: string;
  timestamp: number;
}

export interface EmergencySnapshot {
  location: any;
  battery: any;
  trustScore: number;
  fingerprint: string;
  timestamp: number;
}

export interface DeviceTrustResult {
  trusted: boolean;
  score: number;
  reasons: string[];
  timestamp: number;
}

export interface SecureStorageOptions {
  key: string;
  value?: string;
}

// Register plugin
const KinSafety = registerPlugin<KinSafetyPlugin>('KinSafety', {
  web: () => import('./web').then(m => m.KinSafetyWeb),
});

export default KinSafety;
