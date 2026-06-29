# @kin/heartbeat

KIN Heartbeat Plugin - Background monitoring and periodic status updates

## Install

To use npm

```bash
npm install @kin/heartbeat
````

To use yarn

```bash
yarn add @kin/heartbeat
```

Sync native files

```bash
npx cap sync
```

## API

<docgen-index>

* [`start(...)`](#start)
* [`stop()`](#stop)
* [`getStatus()`](#getstatus)
* [`getLastHeartbeat()`](#getlastheartbeat)
* [`setStatus(...)`](#setstatus)
* [`onHeartbeat(...)`](#onheartbeat)
* [Interfaces](#interfaces)

</docgen-index>

<docgen-api>
<!--Update the source file JSDoc comments and rerun docgen to update the docs below-->

### start(...)

```typescript
start(interval: number) => Promise<{ success: boolean; }>
```

| Param          | Type                |
| -------------- | ------------------- |
| **`interval`** | <code>number</code> |

**Returns:** <code>Promise&lt;{ success: boolean; }&gt;</code>

--------------------


### stop()

```typescript
stop() => Promise<{ success: boolean; }>
```

**Returns:** <code>Promise&lt;{ success: boolean; }&gt;</code>

--------------------


### getStatus()

```typescript
getStatus() => Promise<HeartbeatStatus>
```

**Returns:** <code>Promise&lt;<a href="#heartbeatstatus">HeartbeatStatus</a>&gt;</code>

--------------------


### getLastHeartbeat()

```typescript
getLastHeartbeat() => Promise<HeartbeatData | null>
```

**Returns:** <code>Promise&lt;<a href="#heartbeatdata">HeartbeatData</a> | null&gt;</code>

--------------------


### setStatus(...)

```typescript
setStatus(status: 'active' | 'idle' | 'inactive') => Promise<{ success: boolean; }>
```

| Param        | Type                                          |
| ------------ | --------------------------------------------- |
| **`status`** | <code>'active' \| 'idle' \| 'inactive'</code> |

**Returns:** <code>Promise&lt;{ success: boolean; }&gt;</code>

--------------------


### onHeartbeat(...)

```typescript
onHeartbeat(callback: (data: HeartbeatData) => void) => void
```

| Param          | Type                                                                       |
| -------------- | -------------------------------------------------------------------------- |
| **`callback`** | <code>(data: <a href="#heartbeatdata">HeartbeatData</a>) =&gt; void</code> |

--------------------


### Interfaces


#### HeartbeatStatus

| Prop                | Type                                          |
| ------------------- | --------------------------------------------- |
| **`status`**        | <code>'active' \| 'idle' \| 'inactive'</code> |
| **`lastHeartbeat`** | <code>string \| null</code>                   |
| **`interval`**      | <code>number</code>                           |
| **`isRunning`**     | <code>boolean</code>                          |


#### HeartbeatData

| Prop            | Type                                                  |
| --------------- | ----------------------------------------------------- |
| **`timestamp`** | <code>string</code>                                   |
| **`status`**    | <code>string</code>                                   |
| **`location`**  | <code>{ latitude: number; longitude: number; }</code> |
| **`battery`**   | <code>number</code>                                   |
| **`network`**   | <code>string</code>                                   |

</docgen-api>
