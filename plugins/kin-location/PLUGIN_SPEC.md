# KIN Location Plugin

## Purpose
Provide device location services for the KIN safety platform.

## Public API
### getCurrentLocation()
Returns the current device location.

**Inputs:** None
**Outputs:** `{ latitude, longitude, accuracy, timestamp }`
**Errors:** `LOCATION_DISABLED`, `PERMISSION_DENIED`, `GPS_UNAVAILABLE`, `TIMEOUT`

### startTracking()
Starts background location tracking.

**Inputs:** `{ interval: number, priority: 'high'|'balanced'|'low' }`
**Outputs:** `{ success: boolean }`
**Errors:** `PERMISSION_DENIED`, `SERVICE_UNAVAILABLE`

### stopTracking()
Stops background location tracking.

**Inputs:** None
**Outputs:** `{ success: boolean }`

### checkPermissions()
Checks location permissions status.

**Inputs:** None
**Outputs:** `{ location: 'granted'|'denied'|'prompt' }`

### requestPermissions()
Requests location permissions from the user.

**Inputs:** None
**Outputs:** `{ location: 'granted'|'denied' }`

## Events
### locationChanged
Emitted when location changes during tracking.

**Payload:** `{ latitude, longitude, accuracy, timestamp }`

### trackingError
Emitted when a tracking error occurs.

**Payload:** `{ code: string, message: string }`

## Errors
- `LOCATION_DISABLED`: Device location services are off
- `PERMISSION_DENIED`: User denied location permission
- `GPS_UNAVAILABLE`: GPS hardware is unavailable
- `TIMEOUT`: Location request timed out
- `SERVICE_UNAVAILABLE`: Background tracking service failed

## Dependencies
- Google Play Services (for FusedLocationProviderClient)
- Android Permissions: ACCESS_FINE_LOCATION, ACCESS_BACKGROUND_LOCATION

## Version
1.0.0
