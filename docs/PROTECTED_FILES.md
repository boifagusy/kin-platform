# KIN Protected Files

## Purpose

These files are considered production-critical.
Changes require additional verification.

## Backend Protected Files

| File | Reason |
|------|--------|
| routes/api.php | Production API routes |
| app/Http/Controllers/Api/V1/AuthController.php | Authentication core |
| app/Models/User.php | User data model |

## Frontend Protected Files

| File | Reason |
|------|--------|
| src/main.jsx | Production entry point |
| src/App.tsx | Production routing owner |
| src/services/api.js | API communication layer |

## Navigation Protected Files

| File | Reason |
|------|--------|
| DashboardScreenV2 | Main dashboard |
| NetworkScreenV2 | Network page |
| AlertsScreenV2 | Alerts page |
| ProfileScreen | Profile page |

## Settings Protected Files

| File | Reason |
|------|--------|
| CheckInSettingsScreen | Check-in configuration |
| DuressPinSetupScreen | Duress PIN setup |

## Modification Protocol

Before modifying any protected file:

1. Produce modification report
2. Explain why edit is required
3. Verify build afterwards
4. Test affected routes
5. Backup before changes

## Violation Example

❌ Editing App.jsx (orphan) instead of App.tsx (production)
❌ Adding routes to router.jsx instead of App.tsx
❌ Modifying ProfileScreenV2 (orphan) instead of ProfileScreen (production)
