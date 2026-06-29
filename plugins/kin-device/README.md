# @kin/device

KIN Device Plugin - Battery, network, charging, and device health monitoring

## Install

To use npm

```bash
npm install @kin/device
````

To use yarn

```bash
yarn add @kin/device
```

Sync native files

```bash
npx cap sync
```

## API

<docgen-index>

* [`getBatteryInfo()`](#getbatteryinfo)
* [`getNetworkInfo()`](#getnetworkinfo)
* [`getDeviceInfo()`](#getdeviceinfo)
* [`isCharging()`](#ischarging)
* [`getCarrier()`](#getcarrier)
* [Interfaces](#interfaces)

</docgen-index>

<docgen-api>
<!--Update the source file JSDoc comments and rerun docgen to update the docs below-->

### getBatteryInfo()

```typescript
getBatteryInfo() => Promise<BatteryInfo>
```

**Returns:** <code>Promise&lt;<a href="#batteryinfo">BatteryInfo</a>&gt;</code>

--------------------


### getNetworkInfo()

```typescript
getNetworkInfo() => Promise<NetworkInfo>
```

**Returns:** <code>Promise&lt;<a href="#networkinfo">NetworkInfo</a>&gt;</code>

--------------------


### getDeviceInfo()

```typescript
getDeviceInfo() => Promise<DeviceInfo>
```

**Returns:** <code>Promise&lt;<a href="#deviceinfo">DeviceInfo</a>&gt;</code>

--------------------


### isCharging()

```typescript
isCharging() => Promise<{ isCharging: boolean; }>
```

**Returns:** <code>Promise&lt;{ isCharging: boolean; }&gt;</code>

--------------------


### getCarrier()

```typescript
getCarrier() => Promise<{ carrier: string | null; }>
```

**Returns:** <code>Promise&lt;{ carrier: string | null; }&gt;</code>

--------------------


### Interfaces


#### BatteryInfo

| Prop               | Type                                                     |
| ------------------ | -------------------------------------------------------- |
| **`level`**        | <code>number</code>                                      |
| **`isCharging`**   | <code>boolean</code>                                     |
| **`chargingType`** | <code>'ac' \| 'usb' \| 'wireless' \| 'unknown'</code>    |
| **`health`**       | <code>'unknown' \| 'good' \| 'overheat' \| 'dead'</code> |
| **`temperature`**  | <code>number</code>                                      |


#### NetworkInfo

| Prop                 | Type                                                                                  |
| -------------------- | ------------------------------------------------------------------------------------- |
| **`type`**           | <code>'unknown' \| 'wifi' \| 'cellular' \| 'ethernet' \| 'bluetooth' \| 'none'</code> |
| **`connected`**      | <code>boolean</code>                                                                  |
| **`carrier`**        | <code>string</code>                                                                   |
| **`signalStrength`** | <code>number</code>                                                                   |
| **`ipAddress`**      | <code>string</code>                                                                   |
| **`ssid`**           | <code>string</code>                                                                   |


#### DeviceInfo

| Prop               | Type                 |
| ------------------ | -------------------- |
| **`model`**        | <code>string</code>  |
| **`manufacturer`** | <code>string</code>  |
| **`osVersion`**    | <code>string</code>  |
| **`osName`**       | <code>string</code>  |
| **`isEmulator`**   | <code>boolean</code> |
| **`totalMemory`**  | <code>number</code>  |
| **`freeMemory`**   | <code>number</code>  |

</docgen-api>
