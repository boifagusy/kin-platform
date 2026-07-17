import { Geolocation } from '@capacitor/geolocation';
import { Capacitor } from '@capacitor/core';

export async function getCurrentLocation() {
  // Use Capacitor's native Geolocation when running in a native app context
  if (Capacitor.isNativePlatform()) {
    try {
      const position = await Geolocation.getCurrentPosition({
        enableHighAccuracy: true,
        timeout: 15000,
        maximumAge: 0,
      });


      return {
        latitude: position.coords.latitude,
        longitude: position.coords.longitude,
        accuracy: position.coords.accuracy,
        timestamp: new Date(position.timestamp).toISOString(),
      };
    } catch (error) {
      console.error("Capacitor Geolocation error:", error);
      throw error;
    }
  }

  // Fallback: browser geolocation (used in dev/web testing)
  return new Promise((resolve, reject) => {
    if (!navigator.geolocation) {
      reject(new Error('Geolocation not supported'));
      return;
    }

    navigator.geolocation.getCurrentPosition(
      (position) => {
        resolve({
          latitude: position.coords.latitude,
          longitude: position.coords.longitude,
          accuracy: position.coords.accuracy,
          timestamp: new Date(position.timestamp).toISOString(),
        });
      },
      (error) => {
        console.error("Geolocation error:", error);
        reject(error);
      },
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0,
      }
    );
  });
}

export async function getBatteryLevel() {
  if ('getBattery' in navigator) {
    try {
      const battery = await navigator.getBattery();
      const level = Math.round(battery.level * 100);
      return level;
    } catch (err) {
      console.warn('Battery API error:', err);
      return null;
    }
  }
  return null;
}
