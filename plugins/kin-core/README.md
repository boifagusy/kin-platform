# @kin/core

KIN Core Plugin - Shared utilities and native bridge

## Install

To use npm

```bash
npm install @kin/core
````

To use yarn

```bash
yarn add @kin/core
```

Sync native files

```bash
npx cap sync
```

## API

<docgen-index>

* [`getAppVersion()`](#getappversion)
* [`getDeviceInfo()`](#getdeviceinfo)
* [`getPlatform()`](#getplatform)
* [`isNative()`](#isnative)
* [`getBuildNumber()`](#getbuildnumber)
* [Interfaces](#interfaces)

</docgen-index>

<docgen-api>
<!--Update the source file JSDoc comments and rerun docgen to update the docs below-->

### getAppVersion()

```typescript
getAppVersion() => Promise<{ version: string; build: string; }>
```

**Returns:** <code>Promise&lt;{ version: string; build: string; }&gt;</code>

--------------------


### getDeviceInfo()

```typescript
getDeviceInfo() => Promise<DeviceInfo>
```

**Returns:** <code>Promise&lt;<a href="#deviceinfo">DeviceInfo</a>&gt;</code>

--------------------


### getPlatform()

```typescript
getPlatform() => Promise<{ platform: 'android' | 'ios' | 'web'; }>
```

**Returns:** <code>Promise&lt;{ platform: 'android' | 'ios' | 'web'; }&gt;</code>

--------------------


### isNative()

```typescript
isNative() => Promise<{ isNative: boolean; }>
```

**Returns:** <code>Promise&lt;{ isNative: boolean; }&gt;</code>

--------------------


### getBuildNumber()

```typescript
getBuildNumber() => Promise<{ buildNumber: string; }>
```

**Returns:** <code>Promise&lt;{ buildNumber: string; }&gt;</code>

--------------------


### Interfaces


#### DeviceInfo

| Prop              | Type                                     |
| ----------------- | ---------------------------------------- |
| **`platform`**    | <code>'android' \| 'ios' \| 'web'</code> |
| **`model`**       | <code>string</code>                      |
| **`osVersion`**   | <code>string</code>                      |
| **`appVersion`**  | <code>string</code>                      |
| **`buildNumber`** | <code>string</code>                      |
| **`isNative`**    | <code>boolean</code>                     |

</docgen-api>
