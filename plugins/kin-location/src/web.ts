import { WebPlugin } from '@capacitor/core';
import type {
  KinLocationPlugin,
  LocationResult,
  EmergencySnapshot,
  Confidence,
  Diagnostics,
  InvestigationStatus,
  EmergencyEvidence,
  LocationTimelineEntry,
} from './definitions';

export class KinLocationWeb extends WebPlugin implements KinLocationPlugin {
  private lastLocation: LocationResult | null = null;
  private locationHistory: LocationTimelineEntry[] = [];
  private isTracking = false;
  private watchId: number | null = null;

  async getCurrentLocation(): Promise<LocationResult> {
    return new Promise((resolve, reject) => {
      if (!navigator.geolocation) {
        reject(new Error('Geolocation is not supported'));
        return;
      }

      navigator.geolocation.getCurrentPosition(
        (position) => {
          const result = this.createLocationResult(position);
          this.lastLocation = result;
          resolve(result);
        },
        (error) => {
          reject(new Error(error.message));
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
      );
    });
  }

  async getBestAvailableLocation(): Promise<LocationResult> {
    return this.getCurrentLocation();
  }

  async getEmergencySnapshot(): Promise<EmergencySnapshot> {
    const location = await this.getCurrentLocation();
    return {
      location: {
        latitude: location.location.latitude,
        longitude: location.location.longitude,
        accuracy: location.location.accuracy,
        provider: location.provider.provider,
        confidence: location.confidence.score,
        timestamp: location.timestamp,
      },
      confidence: {
        location: location.confidence.score,
        safety: 50,
        emergency: 50,
        motion: 50,
      },
      motion: {
        state: 'unknown',
        confidence: 50,
        sensors: [],
      },
      context: {
        activity: 'unknown',
        charging: false,
        battery: 100,
        network: {
          available: navigator.onLine,
          carrier: null,
          signalStrength: 3,
        },
      },
      timeline: {
        entries: {},
        count: 0,
        startTime: new Date().toISOString(),
        endTime: new Date().toISOString(),
      },
      providerHealth: {},
      reliability: 50,
      timestamp: new Date().toISOString(),
    };
  }

  async getLocationConfidence(): Promise<Confidence> {
    if (!this.lastLocation) {
      return {
        score: 0,
        level: 'none',
        source: 'none',
        age: 0,
        timestamp: new Date().toISOString(),
      };
    }
    return this.lastLocation.confidence;
  }

  async getLastKnownLocation(): Promise<LocationResult | null> {
    return this.lastLocation;
  }

  async getCachedEmergencyLocation(): Promise<LocationResult | null> {
    return this.lastLocation;
  }

  async isLocationAvailable(): Promise<{ available: boolean }> {
    return { available: !!this.lastLocation };
  }

  async getLocationProvider(): Promise<{
    provider: LocationSource;
    available: boolean;
    enabled: boolean;
    lastUpdate: string | null;
  }> {
    return {
      provider: this.lastLocation?.provider.provider || 'none',
      available: !!this.lastLocation,
      enabled: !!this.lastLocation,
      lastUpdate: this.lastLocation?.timestamp || null,
    };
  }

  async getGooglePlayServicesStatus(): Promise<{
    available: boolean;
    version: string;
    locationEnabled: boolean;
    permissions: {
      fine: 'granted' | 'denied' | 'prompt';
      coarse: 'granted' | 'denied' | 'prompt';
      background: 'granted' | 'denied' | 'prompt';
    };
  }> {
    return {
      available: true,
      version: '21.3.0',
      locationEnabled: !!this.lastLocation,
      permissions: {
        fine: 'granted',
        coarse: 'granted',
        background: 'granted',
      },
    };
  }

  async getLocationDiagnostics(): Promise<Diagnostics> {
    return {
      timestamp: new Date().toISOString(),
      locationServices: {
        enabled: true,
        mode: 'high_accuracy',
      },
      permissions: {
        fine: 'granted',
        coarse: 'granted',
        background: 'granted',
      },
      googlePlayServices: {
        available: true,
        version: '21.3.0',
      },
      batteryOptimization: {
        enabled: false,
        dozeMode: false,
        batteryLevel: 100,
        charging: true,
      },
      network: {
        connected: navigator.onLine,
        type: 'wifi',
        carrier: null,
      },
      gps: {
        enabled: true,
        lastFix: this.lastLocation?.timestamp || null,
        satellites: 12,
      },
      providers: {
        fused: { available: true, enabled: true, lastSuccess: this.lastLocation?.timestamp || null, failureReason: null },
        gps: { available: true, enabled: true, lastSuccess: this.lastLocation?.timestamp || null, failureReason: null },
        network: { available: true, enabled: true, lastSuccess: null, failureReason: null },
        cell: { available: true, enabled: true, lastSuccess: null, failureReason: null },
        wifi: { available: true, enabled: true, lastSuccess: null, failureReason: null },
      },
      lastLocation: this.lastLocation,
      errors: {},
      warnings: {},
    };
  }

  async startTracking(options: {
    interval: number;
    priority: 'high' | 'balanced' | 'low';
  }): Promise<{ success: boolean }> {
    if (this.isTracking) {
      return { success: true };
    }

    if (!navigator.geolocation) {
      throw new Error('Geolocation is not supported');
    }

    this.isTracking = true;
    this.watchId = navigator.geolocation.watchPosition(
      (position) => {
        const result = this.createLocationResult(position);
        this.lastLocation = result;
        this.locationHistory.push({
          timestamp: result.timestamp,
          source: result.provider.provider,
          latitude: result.location.latitude,
          longitude: result.location.longitude,
          accuracy: result.location.accuracy,
          confidence: result.confidence,
          motion: 'unknown',
        });
        if (this.locationHistory.length > 100) {
          this.locationHistory.shift();
        }
        this.notifyListeners('locationChanged', result);
      },
      (error) => {
        this.notifyListeners('trackingError', {
          code: error.code,
          message: error.message,
          timestamp: new Date().toISOString(),
        });
      },
      {
        enableHighAccuracy: options.priority === 'high',
        timeout: 10000,
        maximumAge: options.interval,
      }
    );

    return { success: true };
  }

  async stopTracking(): Promise<{ success: boolean }> {
    if (this.watchId !== null) {
      navigator.geolocation.clearWatch(this.watchId);
      this.watchId = null;
    }
    this.isTracking = false;
    return { success: true };
  }

  async checkPermissions(): Promise<{ location: 'granted' | 'denied' | 'prompt' }> {
    if (!navigator.permissions) {
      return { location: 'granted' };
    }

    try {
      const status = await navigator.permissions.query({ name: 'geolocation' });
      return { location: status.state as 'granted' | 'denied' | 'prompt' };
    } catch {
      return { location: 'prompt' };
    }
  }

  async requestPermissions(): Promise<{ location: 'granted' | 'denied' }> {
    try {
      const status = await navigator.permissions.query({ name: 'geolocation' });
      if (status.state === 'prompt') {
        await this.getCurrentLocation();
      }
      const newStatus = await navigator.permissions.query({ name: 'geolocation' });
      return { location: newStatus.state as 'granted' | 'denied' };
    } catch {
      return { location: 'denied' };
    }
  }

  async getInvestigationStatus(): Promise<InvestigationStatus> {
    return {
      state: 'normal',
      duration: 0,
      triggeredBy: 'none',
      evidence: {
        hasLocation: !!this.lastLocation,
        hasMotion: false,
        hasContext: false,
        confidence: this.lastLocation?.confidence.score || 0,
      },
    };
  }

  async getEmergencyEvidence(options: { emergencyId: string }): Promise<EmergencyEvidence> {
    return {
      emergencyId: options.emergencyId,
      hash: 'sha256:mock_hash',
      timestamp: new Date().toISOString(),
      evidence: {
        locationTimeline: this.locationHistory.slice(-20),
        motionTimeline: [],
        batteryState: {
          level: 100,
          charging: true,
          lastCharge: new Date().toISOString(),
        },
        networkState: {
          available: navigator.onLine,
          carrier: null,
          signalStrength: 3,
          wifi: true,
        },
        providerUsed: this.lastLocation?.provider.provider || 'none',
        confidenceScores: {
          location: this.lastLocation?.confidence.score || 0,
          motion: 50,
          emergency: 50,
        },
        deviceTrust: {
          score: 95,
          isTrusted: true,
        },
        sensorSnapshot: {
          accelerometer: { x: 0, y: 0, z: 9.8 },
          gyroscope: { x: 0, y: 0, z: 0 },
          magnetometer: { x: 30, y: 0, z: -10 },
        },
        diagnostics: await this.getLocationDiagnostics(),
      },
      verification: {
        signed: false,
      },
    };
  }

  async getLocationTimeline(options: {
    limit?: number;
    since?: string;
  }): Promise<{
    entries: Record<string, LocationTimelineEntry>;
    count: number;
    startTime: string;
    endTime: string;
  }> {
    const limit = options.limit || 20;
    const history = this.locationHistory.slice(-limit);
    const entries: Record<string, LocationTimelineEntry> = {};
    history.forEach((entry, index) => {
      entries[index.toString()] = entry;
    });
    return {
      entries,
      count: history.length,
      startTime: history.length > 0 ? history[0].timestamp : new Date().toISOString(),
      endTime: history.length > 0 ? history[history.length - 1].timestamp : new Date().toISOString(),
    };
  }

  private createLocationResult(position: GeolocationPosition): LocationResult {
    const coords = position.coords;
    const timestamp = new Date(position.timestamp).toISOString();
    const accuracy = coords.accuracy || 0;

    let score = 95;
    if (accuracy > 100) score = 60;
    if (accuracy > 200) score = 40;
    if (accuracy > 500) score = 20;

    return {
      location: {
        latitude: coords.latitude,
        longitude: coords.longitude,
        accuracy: accuracy,
        altitude: coords.altitude || undefined,
        speed: coords.speed || undefined,
        bearing: coords.heading || undefined,
      },
      confidence: {
        score: score,
        level: score >= 80 ? 'high' : score >= 50 ? 'medium' : score >= 20 ? 'low' : 'none',
        source: 'fused',
        age: 0,
        timestamp: timestamp,
      },
      provider: {
        provider: 'fused',
        available: true,
        enabled: true,
        lastUpdate: timestamp,
      },
      timestamp: timestamp,
    };
  }
}
