# KIN API Registry

## Authentication Endpoints
| Endpoint | Method | Controller | Request | Response | Status |
|----------|--------|------------|---------|----------|--------|
| /api/v1/auth/confirm-phone | POST | AuthController@confirmPhone | { phone } | { success, exists, next_step } | Production |
| /api/v1/auth/login-pin | POST | AuthController@loginPin | { phone, pin } | { success, token, user } | Production |
| /api/v1/auth/create-pin | POST | AuthController@createPin | { phone, pin } | { success, token, user } | Production |

## Response Contracts
### confirm-phone (Existing User)
{"success":true,"exists":true,"masked_phone":"+234******8522","next_step":"login_pin"}

### confirm-phone (New User)
{"success":true,"exists":false,"masked_phone":"+234******9999","next_step":"register"}

## Breaking Changes Policy
- Never rename response fields
- Never remove response fields
- Never change next_step values
- Always maintain backward compatibility
