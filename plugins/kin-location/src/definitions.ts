export interface Location {
  latitude: number;
  longitude: number;
  accuracy: number;
  altitude?: number;
  speed?: number;
  bearing?: number;
}

export type LocationSource =
  | 'fused'
  | 'gps'
  | 'network'
  | 'cell'
  | 'wifi'
  | 'cached'
  | 'emergency'
  | 'manual'
  | 'none';

export type ConfidenceLevel = 'high' | 'medium' | 'low' | 'none';

export type MotionState = 'stationary' | 'walking' | 'running' | 'cycling' | 'driving' | 'unknown';

export type InvestigationState = 'normal' | 'concern' | 'investigation' | 'emergency' | 'recovery';

export interface Confidence {
  score: number;
  level: ConfidenceLevel;
  source: LocationSource;
  age: number;
  timestamp: string;
}

export interface LocationResult {
  location: Location;
  confidence: Confidence;
  provider: {
    provider: LocationSource;
    available: boolean;
    enabled: boolean;
    lastUpdate: string | null;
  };
  timestamp: string;
}

export interface EmergencySnapshot {
  location: {
    latitude: number;
    longitude: number;
    accuracy: number;
    provider: LocationSource;
    confidence: number;
    timestamp: string;
  };
  confidence: {
    location: number;
    safety: number;
    emergency: number;
    motion: number;
  };
  motion: {
    state: MotionState;
    confidence: number;
    sensors: string[];
  };
  context: {
    activity: string;
    charging: boolean;
    battery: number;
    network: {
      available: boolean;
      carrier: string | null;
      signalStrength: number;
    };
  };
  timeline: {
    entries: Record<string, LocationTimelineEntry>;
    count: number;
    startTime: string;
    endTime: string;
  };
  providerHealth: Record<string, ProviderHealth>;
  reliability: number;
  timestamp: string;
}

export interface LocationTimelineEntry {
  timestamp: string;
  source: LocationSource;
  latitude: number;
  longitude: number;
  accuracy: number;
  confidence: Confidence;
  motion: MotionState;
}

export interface ProviderHealth {
  health: number;
  status: 'healthy' | 'degraded' | 'unavailable' | 'unhealthy';
  lastFix: string | null;
  avgAccuracy: number;
}

export interface Diagnostics {
  timestamp: string;
  locationServices: {
    enabled: boolean;
    mode: string;
  };
  permissions: {
    fine: 'granted' | 'denied' | 'prompt';
    coarse: 'granted' | 'denied' | 'prompt';
    background: 'granted' | 'denied' | 'prompt';
  };
  googlePlayServices: {
    available: boolean;
    version: string;
  };
  batteryOptimization: {
    enabled: boolean;
    dozeMode: boolean;
    batteryLevel: number;
    charging: boolean;
  };
  network: {
    connected: boolean;
    type: string | null;
    carrier: string | null;
  };
  gps: {
    enabled: boolean;
    lastFix: string | null;
    satellites: number;
  };
  providers: Record<string, ProviderStatus>;
  lastLocation: LocationResult | null;
  errors: Record<string, string>;
  warnings: Record<string, string>;
}

export interface ProviderStatus {
  available: boolean;
  enabled: boolean;
  lastSuccess: string | null;
  failureReason: string | null;
}

export interface InvestigationStatus {
  state: InvestigationState;
  duration: number;
  triggeredBy: string;
  evidence: {
    hasLocation: boolean;
    hasMotion: boolean;
    hasContext: boolean;
    confidence: number;
  };
}

export interface EmergencyEvidence {
  emergencyId: string;
  hash: string;
  timestamp: string;
  evidence: {
    locationTimeline: LocationTimelineEntry[];
    motionTimeline: MotionState[];
    batteryState: {
      level: number;
      charging: boolean;
      lastCharge: string;
    };
    networkState: {
      available: boolean;
      carrier: string | null;
      signalStrength: number;
      wifi: boolean;
    };
    providerUsed: LocationSource;
    confidenceScores: {
      location: number;
      motion: number;
      emergency: number;
    };
    deviceTrust: {
      score: number;
      isTrusted: boolean;
    };
    sensorSnapshot: {
      accelerometer: { x: number; y: number; z: number };
      gyroscope: { x: number; y: number; z: number };
      magnetometer: { x: number; y: number; z: number };
    };
    diagnostics: Diagnostics;
  };
  verification: {
    signed: boolean;
    signature?: string;
  };
}

export interface KinLocationPlugin {
  // Core Location Methods
  getCurrentLocation(): Promise<LocationResult>;
  getBestAvailableLocation(): Promise<LocationResult>;
  getEmergencySnapshot(): Promise<EmergencySnapshot>;
  getLocationConfidence(): Promise<Confidence>;
  getLastKnownLocation(): Promise<LocationResult | null>;
  getCachedEmergencyLocation(): Promise<LocationResult | null>;
  isLocationAvailable(): Promise<{ available: boolean }>;
  getLocationProvider(): Promise<{
    provider: LocationSource;
    available: boolean;
    enabled: boolean;
    lastUpdate: string | null;
  }>;
  getGooglePlayServicesStatus(): Promise<{
    available: boolean;
    version: string;
    locationEnabled: boolean;
    permissions: {
      fine: 'granted' | 'denied' | 'prompt';
      coarse: 'granted' | 'denied' | 'prompt';
      background: 'granted' | 'denied' | 'prompt';
    };
  }>;
  getLocationDiagnostics(): Promise<Diagnostics>;

  // Tracking Methods
  startTracking(options: { interval: number; priority: 'high' | 'balanced' | 'low' }): Promise<{ success: boolean }>;
  stopTracking(): Promise<{ success: boolean }>;

  // Permission Methods
  checkPermissions(): Promise<{ location: 'granted' | 'denied' | 'prompt' }>;
  requestPermissions(): Promise<{ location: 'granted' | 'denied' }>;

  // Investigation Methods
  getInvestigationStatus(): Promise<InvestigationStatus>;
  getEmergencyEvidence(options: { emergencyId: string }): Promise<EmergencyEvidence>;
  getLocationTimeline(options: { limit?: number; since?: string }): Promise<{
    entries: Record<string, LocationTimelineEntry>;
    count: number;
    startTime: string;
    endTime: string;
  }>;
}
