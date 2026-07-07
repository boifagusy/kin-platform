# KIN Dependency Map

## Authentication Flow
PhoneEntryScreenV2
    │
    ▼
POST /api/v1/auth/confirm-phone
    │
    ▼
AuthController::confirmPhone()
    │
    ▼
ConfirmPhoneAction → User Model (lookup)
    │
    ▼
Response: { exists, next_step }
    │
    ├── exists: true → login_pin → LoginPinScreenV2
    │                                    │
    │                                    ▼
    │                            POST /api/v1/auth/login-pin
    │                                    │
    │                                    ▼
    │                            AuthController::loginPin()
    │                                    │
    │                                    ▼
    │                            LoginPinAction → User Model (verify)
    │                                    │
    │                                    ▼
    │                            Response: { token, user } → Dashboard
    │
    └── exists: false → register → CreatePinScreenV2
                                          │
                                          ▼
                                  POST /api/v1/auth/create-pin
                                          │
                                          ▼
                                  AuthController::createPin()
                                          │
                                          ▼
                                  CreatePinAction → User Model (create)
                                          │
                                          ▼
                                  Response: { token, user } → Dashboard
