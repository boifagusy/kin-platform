# KIN Screen Flow

## Authentication Flow
PhoneEntryScreenV2 (/)
    │
    ▼ POST /api/v1/auth/confirm-phone
    │
    ├── exists: true → LoginPinScreenV2 (/login-pin)
    │                      │
    │                      ▼ POST /api/v1/auth/login-pin
    │                      │
    │                      ▼ Dashboard
    │
    └── exists: false → CreatePinScreenV2 (/create-pin)
                           │
                           ▼ POST /api/v1/auth/create-pin
                           │
                           ▼ UserDetailsScreenV2 (/user-details)
                           │
                           ▼ TrustedContactScreenV2 (/trusted-contact)
                           │
                           ▼ Dashboard

## Screen Mapping
| Screen | Route | API Endpoint | Next Screen |
|--------|-------|--------------|-------------|
| PhoneEntryScreenV2 | / | POST /auth/confirm-phone | login-pin / create-pin |
| LoginPinScreenV2 | /login-pin | POST /auth/login-pin | dashboard |
| CreatePinScreenV2 | /create-pin | POST /auth/create-pin | user-details |
