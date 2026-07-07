import { Geolocation } from '@capacitor/geolocation';
import { Capacitor } from '@capacitor/core';

const API_BASE = import.meta.env.VITE_API_URL || "http://localhost:8000/api/v1";

export async function getCurrentLocation() {
  let position;

  if (Capacitor.isNativePlatform()) {
    // Native Android — use Capacitor plugin (triggers real Android permission dialog)
    const permission = await Geolocation.requestPermissions();
    if (permission.location !== 'granted') {
      throw new Error('Location permission denied');
    }
    position = await Geolocation.getCurrentPosition({
      enableHighAccuracy: true,
      timeout: 15000,
      maximumAge: 0,
    });
  } else {
    // Browser fallback (dev/testing in Chrome)
    position = await new Promise((resolve, reject) => {
      if (!navigator.geolocation) {
        reject(new Error('Geolocation not supported'));
        return;
      }
      navigator.geolocation.getCurrentPosition(resolve, reject, {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0,
      });
    });
  }

  const coords = {
    latitude: position.coords.latitude,
    longitude: position.coords.longitude,
    accuracy: position.coords.accuracy,
    speed: position.coords.speed || null,
    heading: position.coords.heading || null,
    timestamp: position.timestamp,
  };

  // Send to backend silently — don't block on failure
  sendLocationToBackend(coords).catch(err =>
    console.warn("Location sync failed:", err.message)
  );

  return coords;
}

async function sendLocationToBackend(coords) {
  const token = localStorage.getItem("kin_token");
  if (!token) return;

  await fetch(`${API_BASE}/location`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "Authorization": `Bearer ${token}`,
      "Accept": "application/json",
    },
    body: JSON.stringify(coords),
  });
}

export async function getBatteryLevel() {
  if ('getBattery' in navigator) {
    try {
      const battery = await navigator.getBattery();
      return Math.round(battery.level * 100);
    } catch (err) {
      console.warn('Battery API error:', err);
      return null;
    }
  }
  return null;
}
