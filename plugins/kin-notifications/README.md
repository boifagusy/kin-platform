# @kin/notifications

KIN Notifications Plugin - Local and push notifications for SOS alerts and check-in reminders

## Install

To use npm

```bash
npm install @kin/notifications
````

To use yarn

```bash
yarn add @kin/notifications
```

Sync native files

```bash
npx cap sync
```

## API

<docgen-index>

* [`schedule(...)`](#schedule)
* [`cancel(...)`](#cancel)
* [`cancelAll()`](#cancelall)
* [`getScheduled()`](#getscheduled)
* [`checkPermissions()`](#checkpermissions)
* [`requestPermissions()`](#requestpermissions)
* [`onNotificationClick(...)`](#onnotificationclick)
* [`onNotificationAction(...)`](#onnotificationaction)
* [Interfaces](#interfaces)
* [Type Aliases](#type-aliases)

</docgen-index>

<docgen-api>
<!--Update the source file JSDoc comments and rerun docgen to update the docs below-->

### schedule(...)

```typescript
schedule(notification: Notification) => Promise<{ id: string; }>
```

| Param              | Type                                                  |
| ------------------ | ----------------------------------------------------- |
| **`notification`** | <code><a href="#notification">Notification</a></code> |

**Returns:** <code>Promise&lt;{ id: string; }&gt;</code>

--------------------


### cancel(...)

```typescript
cancel(notificationId: string) => Promise<{ success: boolean; }>
```

| Param                | Type                |
| -------------------- | ------------------- |
| **`notificationId`** | <code>string</code> |

**Returns:** <code>Promise&lt;{ success: boolean; }&gt;</code>

--------------------


### cancelAll()

```typescript
cancelAll() => Promise<{ success: boolean; }>
```

**Returns:** <code>Promise&lt;{ success: boolean; }&gt;</code>

--------------------


### getScheduled()

```typescript
getScheduled() => Promise<{ notifications: Notification[]; }>
```

**Returns:** <code>Promise&lt;{ notifications: Notification[]; }&gt;</code>

--------------------


### checkPermissions()

```typescript
checkPermissions() => Promise<{ granted: boolean; }>
```

**Returns:** <code>Promise&lt;{ granted: boolean; }&gt;</code>

--------------------


### requestPermissions()

```typescript
requestPermissions() => Promise<{ granted: boolean; }>
```

**Returns:** <code>Promise&lt;{ granted: boolean; }&gt;</code>

--------------------


### onNotificationClick(...)

```typescript
onNotificationClick(callback: (notification: Notification) => void) => void
```

| Param          | Type                                                                             |
| -------------- | -------------------------------------------------------------------------------- |
| **`callback`** | <code>(notification: <a href="#notification">Notification</a>) =&gt; void</code> |

--------------------


### onNotificationAction(...)

```typescript
onNotificationAction(callback: (action: NotificationAction) => void) => void
```

| Param          | Type                                                                                   |
| -------------- | -------------------------------------------------------------------------------------- |
| **`callback`** | <code>(action: <a href="#notificationaction">NotificationAction</a>) =&gt; void</code> |

--------------------


### Interfaces


#### Notification

| Prop                    | Type                                                         |
| ----------------------- | ------------------------------------------------------------ |
| **`id`**                | <code>string</code>                                          |
| **`title`**             | <code>string</code>                                          |
| **`body`**              | <code>string</code>                                          |
| **`data`**              | <code><a href="#record">Record</a>&lt;string, any&gt;</code> |
| **`scheduleAt`**        | <code>string</code>                                          |
| **`recurring`**         | <code>boolean</code>                                         |
| **`recurringInterval`** | <code>'daily' \| 'weekly' \| 'monthly'</code>                |
| **`sound`**             | <code>boolean</code>                                         |
| **`vibrate`**           | <code>boolean</code>                                         |
| **`priority`**          | <code>'high' \| 'normal' \| 'low'</code>                     |


#### NotificationAction

| Prop         | Type                                                         |
| ------------ | ------------------------------------------------------------ |
| **`id`**     | <code>string</code>                                          |
| **`title`**  | <code>string</code>                                          |
| **`action`** | <code>'open' \| 'dismiss' \| 'custom'</code>                 |
| **`data`**   | <code><a href="#record">Record</a>&lt;string, any&gt;</code> |


### Type Aliases


#### Record

Construct a type with a set of properties K of type T

<code>{ [P in K]: T; }</code>

</docgen-api>
