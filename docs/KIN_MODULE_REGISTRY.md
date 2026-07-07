# KIN Module Registry

## Backend Modules
| Module ID | Name | File | Status | Risk |
|-----------|------|------|--------|------|
| AUTH-001 | AuthController | app/Http/Controllers/Api/V1/AuthController.php | Production | CRITICAL |
| AUTH-002 | User Model | app/Models/User.php | Production | CRITICAL |
| AUTH-003 | LoginPinAction | app/Actions/Auth/LoginPinAction.php | Production | CRITICAL |
| AUTH-004 | CreatePinAction | app/Actions/Auth/CreatePinAction.php | Production | CRITICAL |
| AUTH-005 | ConfirmPhoneAction | app/Actions/Auth/ConfirmPhoneAction.php | Production | CRITICAL |

## Frontend Modules
| Module ID | Name | File | Status | Risk |
|-----------|------|------|--------|------|
| UI-AUTH-001 | PhoneEntryScreenV2 | src/screens/ui-polish/PhoneEntryScreenV2.jsx | Production | CRITICAL |
| UI-AUTH-002 | LoginPinScreenV2 | src/screens/ui-polish/LoginPinScreenV2.jsx | Production | CRITICAL |
| UI-AUTH-003 | CreatePinScreenV2 | src/screens/ui-polish/CreatePinScreenV2.jsx | Production | CRITICAL |

## Routes
| Route | Method | Controller | Status |
|-------|--------|------------|--------|
| /api/v1/auth/confirm-phone | POST | AuthController@confirmPhone | Production |
| /api/v1/auth/login-pin | POST | AuthController@loginPin | Production |
| /api/v1/auth/create-pin | POST | AuthController@createPin | Production |
