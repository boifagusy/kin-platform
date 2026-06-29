# @kin/security

KIN Security Plugin - Encryption, keystore, and biometrics

## Install

To use npm

```bash
npm install @kin/security
````

To use yarn

```bash
yarn add @kin/security
```

Sync native files

```bash
npx cap sync
```

## API

<docgen-index>

* [`encrypt(...)`](#encrypt)
* [`decrypt(...)`](#decrypt)
* [`storeSecurely(...)`](#storesecurely)
* [`retrieveSecurely(...)`](#retrievesecurely)
* [`deleteSecurely(...)`](#deletesecurely)
* [`checkBiometrics()`](#checkbiometrics)
* [`generateKey(...)`](#generatekey)

</docgen-index>

<docgen-api>
<!--Update the source file JSDoc comments and rerun docgen to update the docs below-->

### encrypt(...)

```typescript
encrypt(data: string) => Promise<{ encrypted: string; }>
```

| Param      | Type                |
| ---------- | ------------------- |
| **`data`** | <code>string</code> |

**Returns:** <code>Promise&lt;{ encrypted: string; }&gt;</code>

--------------------


### decrypt(...)

```typescript
decrypt(encrypted: string) => Promise<{ decrypted: string; }>
```

| Param           | Type                |
| --------------- | ------------------- |
| **`encrypted`** | <code>string</code> |

**Returns:** <code>Promise&lt;{ decrypted: string; }&gt;</code>

--------------------


### storeSecurely(...)

```typescript
storeSecurely(key: string, value: string) => Promise<{ success: boolean; }>
```

| Param       | Type                |
| ----------- | ------------------- |
| **`key`**   | <code>string</code> |
| **`value`** | <code>string</code> |

**Returns:** <code>Promise&lt;{ success: boolean; }&gt;</code>

--------------------


### retrieveSecurely(...)

```typescript
retrieveSecurely(key: string) => Promise<{ value: string | null; }>
```

| Param     | Type                |
| --------- | ------------------- |
| **`key`** | <code>string</code> |

**Returns:** <code>Promise&lt;{ value: string | null; }&gt;</code>

--------------------


### deleteSecurely(...)

```typescript
deleteSecurely(key: string) => Promise<{ success: boolean; }>
```

| Param     | Type                |
| --------- | ------------------- |
| **`key`** | <code>string</code> |

**Returns:** <code>Promise&lt;{ success: boolean; }&gt;</code>

--------------------


### checkBiometrics()

```typescript
checkBiometrics() => Promise<{ available: boolean; enrolled: boolean; }>
```

**Returns:** <code>Promise&lt;{ available: boolean; enrolled: boolean; }&gt;</code>

--------------------


### generateKey(...)

```typescript
generateKey(alias: string) => Promise<{ success: boolean; }>
```

| Param       | Type                |
| ----------- | ------------------- |
| **`alias`** | <code>string</code> |

**Returns:** <code>Promise&lt;{ success: boolean; }&gt;</code>

--------------------

</docgen-api>
