# @kin/location

KIN Location Plugin - GPS and background location tracking

## Install

To use npm

```bash
npm install @kin/location
````

To use yarn

```bash
yarn add @kin/location
```

Sync native files

```bash
npx cap sync
```

## API

<docgen-index>

* [`getCurrentLocation()`](#getcurrentlocation)
* [`getBestAvailableLocation()`](#getbestavailablelocation)
* [`getEmergencySnapshot()`](#getemergencysnapshot)
* [`getLocationConfidence()`](#getlocationconfidence)
* [`getLastKnownLocation()`](#getlastknownlocation)
* [`getCachedEmergencyLocation()`](#getcachedemergencylocation)
* [`isLocationAvailable()`](#islocationavailable)
* [`getLocationProvider()`](#getlocationprovider)
* [`getGooglePlayServicesStatus()`](#getgoogleplayservicesstatus)
* [`getLocationDiagnostics()`](#getlocationdiagnostics)
* [`startTracking(...)`](#starttracking)
* [`stopTracking()`](#stoptracking)
* [`checkPermissions()`](#checkpermissions)
* [`requestPermissions()`](#requestpermissions)
* [`getInvestigationStatus()`](#getinvestigationstatus)
* [`getEmergencyEvidence(...)`](#getemergencyevidence)
* [`getLocationTimeline(...)`](#getlocationtimeline)
* [Interfaces](#interfaces)
* [Type Aliases](#type-aliases)

</docgen-index>

<docgen-api>
<!--Update the source file JSDoc comments and rerun docgen to update the docs below-->

### getCurrentLocation()

```typescript
getCurrentLocation() => Promise<LocationResult>
```

**Returns:** <code>Promise&lt;<a href="#locationresult">LocationResult</a>&gt;</code>

--------------------


### getBestAvailableLocation()

```typescript
getBestAvailableLocation() => Promise<LocationResult>
```

**Returns:** <code>Promise&lt;<a href="#locationresult">LocationResult</a>&gt;</code>

--------------------


### getEmergencySnapshot()

```typescript
getEmergencySnapshot() => Promise<EmergencySnapshot>
```

**Returns:** <code>Promise&lt;<a href="#emergencysnapshot">EmergencySnapshot</a>&gt;</code>

--------------------


### getLocationConfidence()

```typescript
getLocationConfidence() => Promise<Confidence>
```

**Returns:** <code>Promise&lt;<a href="#confidence">Confidence</a>&gt;</code>

--------------------


### getLastKnownLocation()

```typescript
getLastKnownLocation() => Promise<LocationResult | null>
```

**Returns:** <code>Promise&lt;<a href="#locationresult">LocationResult</a> | null&gt;</code>

--------------------


### getCachedEmergencyLocation()

```typescript
getCachedEmergencyLocation() => Promise<LocationResult | null>
```

**Returns:** <code>Promise&lt;<a href="#locationresult">LocationResult</a> | null&gt;</code>

--------------------


### isLocationAvailable()

```typescript
isLocationAvailable() => Promise<{ available: boolean; }>
```

**Returns:** <code>Promise&lt;{ available: boolean; }&gt;</code>

--------------------


### getLocationProvider()

```typescript
getLocationProvider() => Promise<{ provider: LocationSource; available: boolean; enabled: boolean; lastUpdate: string | null; }>
```

**Returns:** <code>Promise&lt;{ provider: <a href="#locationsource">LocationSource</a>; available: boolean; enabled: boolean; lastUpdate: string | null; }&gt;</code>

--------------------


### getGooglePlayServicesStatus()

```typescript
getGooglePlayServicesStatus() => Promise<{ available: boolean; version: string; locationEnabled: boolean; permissions: { fine: 'granted' | 'denied' | 'prompt'; coarse: 'granted' | 'denied' | 'prompt'; background: 'granted' | 'denied' | 'prompt'; }; }>
```

**Returns:** <code>Promise&lt;{ available: boolean; version: string; locationEnabled: boolean; permissions: { fine: 'granted' | 'denied' | 'prompt'; coarse: 'granted' | 'denied' | 'prompt'; background: 'granted' | 'denied' | 'prompt'; }; }&gt;</code>

--------------------


### getLocationDiagnostics()

```typescript
getLocationDiagnostics() => Promise<Diagnostics>
```

**Returns:** <code>Promise&lt;<a href="#diagnostics">Diagnostics</a>&gt;</code>

--------------------


### startTracking(...)

```typescript
startTracking(options: { interval: number; priority: 'high' | 'balanced' | 'low'; }) => Promise<{ success: boolean; }>
```

| Param         | Type                                                                        |
| ------------- | --------------------------------------------------------------------------- |
| **`options`** | <code>{ interval: number; priority: 'high' \| 'low' \| 'balanced'; }</code> |

**Returns:** <code>Promise&lt;{ success: boolean; }&gt;</code>

--------------------


### stopTracking()

```typescript
stopTracking() => Promise<{ success: boolean; }>
```

**Returns:** <code>Promise&lt;{ success: boolean; }&gt;</code>

--------------------


### checkPermissions()

```typescript
checkPermissions() => Promise<{ location: 'granted' | 'denied' | 'prompt'; }>
```

**Returns:** <code>Promise&lt;{ location: 'granted' | 'denied' | 'prompt'; }&gt;</code>

--------------------


### requestPermissions()

```typescript
requestPermissions() => Promise<{ location: 'granted' | 'denied'; }>
```

**Returns:** <code>Promise&lt;{ location: 'granted' | 'denied'; }&gt;</code>

--------------------


### getInvestigationStatus()

```typescript
getInvestigationStatus() => Promise<InvestigationStatus>
```

**Returns:** <code>Promise&lt;<a href="#investigationstatus">InvestigationStatus</a>&gt;</code>

--------------------


### getEmergencyEvidence(...)

```typescript
getEmergencyEvidence(options: { emergencyId: string; }) => Promise<EmergencyEvidence>
```

| Param         | Type                                  |
| ------------- | ------------------------------------- |
| **`options`** | <code>{ emergencyId: string; }</code> |

**Returns:** <code>Promise&lt;<a href="#emergencyevidence">EmergencyEvidence</a>&gt;</code>

--------------------


### getLocationTimeline(...)

```typescript
getLocationTimeline(options: { limit?: number; since?: string; }) => Promise<{ entries: Record<string, LocationTimelineEntry>; count: number; startTime: string; endTime: string; }>
```

| Param         | Type                                             |
| ------------- | ------------------------------------------------ |
| **`options`** | <code>{ limit?: number; since?: string; }</code> |

**Returns:** <code>Promise&lt;{ entries: <a href="#record">Record</a>&lt;string, <a href="#locationtimelineentry">LocationTimelineEntry</a>&gt;; count: number; startTime: string; endTime: string; }&gt;</code>

--------------------


### Interfaces


#### LocationResult

| Prop             | Type                                                                                                                                       |
| ---------------- | ------------------------------------------------------------------------------------------------------------------------------------------ |
| **`location`**   | <code><a href="#location">Location</a></code>                                                                                              |
| **`confidence`** | <code><a href="#confidence">Confidence</a></code>                                                                                          |
| **`provider`**   | <code>{ provider: <a href="#locationsource">LocationSource</a>; available: boolean; enabled: boolean; lastUpdate: string \| null; }</code> |
| **`timestamp`**  | <code>string</code>                                                                                                                        |


#### Location

| Prop            | Type                |
| --------------- | ------------------- |
| **`latitude`**  | <code>number</code> |
| **`longitude`** | <code>number</code> |
| **`accuracy`**  | <code>number</code> |
| **`altitude`**  | <code>number</code> |
| **`speed`**     | <code>number</code> |
| **`bearing`**   | <code>number</code> |


#### Confidence

| Prop            | Type                                                        |
| --------------- | ----------------------------------------------------------- |
| **`score`**     | <code>number</code>                                         |
| **`level`**     | <code><a href="#confidencelevel">ConfidenceLevel</a></code> |
| **`source`**    | <code><a href="#locationsource">LocationSource</a></code>   |
| **`age`**       | <code>number</code>                                         |
| **`timestamp`** | <code>string</code>                                         |


#### EmergencySnapshot

| Prop                 | Type                                                                                                                                                                                 |
| -------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **`location`**       | <code>{ latitude: number; longitude: number; accuracy: number; provider: <a href="#locationsource">LocationSource</a>; confidence: number; timestamp: string; }</code>               |
| **`confidence`**     | <code>{ location: number; safety: number; emergency: number; motion: number; }</code>                                                                                                |
| **`motion`**         | <code>{ state: <a href="#motionstate">MotionState</a>; confidence: number; sensors: string[]; }</code>                                                                               |
| **`context`**        | <code>{ activity: string; charging: boolean; battery: number; network: { available: boolean; carrier: string \| null; signalStrength: number; }; }</code>                            |
| **`timeline`**       | <code>{ entries: <a href="#record">Record</a>&lt;string, <a href="#locationtimelineentry">LocationTimelineEntry</a>&gt;; count: number; startTime: string; endTime: string; }</code> |
| **`providerHealth`** | <code><a href="#record">Record</a>&lt;string, <a href="#providerhealth">ProviderHealth</a>&gt;</code>                                                                                |
| **`reliability`**    | <code>number</code>                                                                                                                                                                  |
| **`timestamp`**      | <code>string</code>                                                                                                                                                                  |


#### LocationTimelineEntry

| Prop             | Type                                                      |
| ---------------- | --------------------------------------------------------- |
| **`timestamp`**  | <code>string</code>                                       |
| **`source`**     | <code><a href="#locationsource">LocationSource</a></code> |
| **`latitude`**   | <code>number</code>                                       |
| **`longitude`**  | <code>number</code>                                       |
| **`accuracy`**   | <code>number</code>                                       |
| **`confidence`** | <code><a href="#confidence">Confidence</a></code>         |
| **`motion`**     | <code><a href="#motionstate">MotionState</a></code>       |


#### ProviderHealth

| Prop              | Type                                                                 |
| ----------------- | -------------------------------------------------------------------- |
| **`health`**      | <code>number</code>                                                  |
| **`status`**      | <code>'healthy' \| 'degraded' \| 'unavailable' \| 'unhealthy'</code> |
| **`lastFix`**     | <code>string \| null</code>                                          |
| **`avgAccuracy`** | <code>number</code>                                                  |


#### Diagnostics

| Prop                      | Type                                                                                                                                                |
| ------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------- |
| **`timestamp`**           | <code>string</code>                                                                                                                                 |
| **`locationServices`**    | <code>{ enabled: boolean; mode: string; }</code>                                                                                                    |
| **`permissions`**         | <code>{ fine: 'granted' \| 'denied' \| 'prompt'; coarse: 'granted' \| 'denied' \| 'prompt'; background: 'granted' \| 'denied' \| 'prompt'; }</code> |
| **`googlePlayServices`**  | <code>{ available: boolean; version: string; }</code>                                                                                               |
| **`batteryOptimization`** | <code>{ enabled: boolean; dozeMode: boolean; batteryLevel: number; charging: boolean; }</code>                                                      |
| **`network`**             | <code>{ connected: boolean; type: string \| null; carrier: string \| null; }</code>                                                                 |
| **`gps`**                 | <code>{ enabled: boolean; lastFix: string \| null; satellites: number; }</code>                                                                     |
| **`providers`**           | <code><a href="#record">Record</a>&lt;string, <a href="#providerstatus">ProviderStatus</a>&gt;</code>                                               |
| **`lastLocation`**        | <code><a href="#locationresult">LocationResult</a> \| null</code>                                                                                   |
| **`errors`**              | <code><a href="#record">Record</a>&lt;string, string&gt;</code>                                                                                     |
| **`warnings`**            | <code><a href="#record">Record</a>&lt;string, string&gt;</code>                                                                                     |


#### ProviderStatus

| Prop                | Type                        |
| ------------------- | --------------------------- |
| **`available`**     | <code>boolean</code>        |
| **`enabled`**       | <code>boolean</code>        |
| **`lastSuccess`**   | <code>string \| null</code> |
| **`failureReason`** | <code>string \| null</code> |


#### InvestigationStatus

| Prop              | Type                                                                                                |
| ----------------- | --------------------------------------------------------------------------------------------------- |
| **`state`**       | <code><a href="#investigationstate">InvestigationState</a></code>                                   |
| **`duration`**    | <code>number</code>                                                                                 |
| **`triggeredBy`** | <code>string</code>                                                                                 |
| **`evidence`**    | <code>{ hasLocation: boolean; hasMotion: boolean; hasContext: boolean; confidence: number; }</code> |


#### EmergencyEvidence

| Prop               | Type                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        |
| ------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **`emergencyId`**  | <code>string</code>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         |
| **`hash`**         | <code>string</code>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         |
| **`timestamp`**    | <code>string</code>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         |
| **`evidence`**     | <code>{ locationTimeline: LocationTimelineEntry[]; motionTimeline: MotionState[]; batteryState: { level: number; charging: boolean; lastCharge: string; }; networkState: { available: boolean; carrier: string \| null; signalStrength: number; wifi: boolean; }; providerUsed: <a href="#locationsource">LocationSource</a>; confidenceScores: { location: number; motion: number; emergency: number; }; deviceTrust: { score: number; isTrusted: boolean; }; sensorSnapshot: { accelerometer: { x: number; y: number; z: number; }; gyroscope: { x: number; y: number; z: number; }; magnetometer: { x: number; y: number; z: number; }; }; diagnostics: <a href="#diagnostics">Diagnostics</a>; }</code> |
| **`verification`** | <code>{ signed: boolean; signature?: string; }</code>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |


### Type Aliases


#### ConfidenceLevel

<code>'high' | 'medium' | 'low' | 'none'</code>


#### LocationSource

<code>'fused' | 'gps' | 'network' | 'cell' | 'wifi' | 'cached' | 'emergency' | 'manual' | 'none'</code>


#### MotionState

<code>'stationary' | 'walking' | 'running' | 'cycling' | 'driving' | 'unknown'</code>


#### Record

Construct a type with a set of properties K of type T

<code>{ [P in K]: T; }</code>


#### InvestigationState

<code>'normal' | 'concern' | 'investigation' | 'emergency' | 'recovery'</code>

</docgen-api>
