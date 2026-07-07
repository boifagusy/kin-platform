# KIN Database Map

## Users Table (Critical Fields)
| Field | Type | Purpose | Never Remove |
|-------|------|---------|--------------|
| phone | string | User identifier | ✅ |
| login_pin_hash | string | PIN authentication | ✅ |
| onboarding_completed | boolean | Onboarding status | ✅ |
| phone_verified_at | timestamp | Phone verification | ✅ |
| status | string | Account status | ✅ |

## Relationships
- User hasMany(CheckIn)
- User hasMany(ActivityLog)
- User hasMany(TrustedContact)
- User hasMany(SosEvent)
