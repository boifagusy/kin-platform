# KIN PLATFORM — SYSTEM BIBLE
**Version:** 3.0.0 | **Updated:** 2026-06-18 | **Status:** MVP STABILIZATION

## SYSTEM OVERVIEW
KIN — Personal Safety Platform
- **Backend:** Laravel 12 (PHP 8+)
- **Frontend:** React + Vite
- **Database:** SQLite (Development)
- **Mobile:** Termux (Android)

## BACKEND STATISTICS (Verified from Code)
| Category | Count |
|----------|-------|
| Controllers | 24 (8 Admin + 15 API + 1 Base) |
| Models | 16 |
| Services | 12 (6 Admin + 6 Core) |
| Jobs | 3 |
| Listeners | 4 |
| Events | 2 |

## DATABASE STATISTICS
| Category | Count |
|----------|-------|
| Tables | 26 (21 Custom + 5 Framework) |
| Migrations | 33 |
| Missing Model | admin_password_resets |

## FRONTEND STATISTICS
| Category | Count |
|----------|-------|
| Screens | 10 |
| Components | 40 |
| Services | 6 |

## API ENDPOINTS (33 Total)
### Authentication (10)
POST /api/v1/auth/login
POST /api/v1/auth/login-pin
POST /api/v1/auth/create-pin
POST /api/v1/auth/complete-onboarding
POST /api/v1/auth/confirm-phone
POST /api/v1/auth/user-details
POST /api/v1/auth/trusted-contact
POST /api/v1/forgot-pin/send-otp
POST /api/v1/forgot-pin/verify-otp
POST /api/v1/forgot-pin/reset

### Check-In (4)
POST /api/v1/checkin
GET /api/v1/checkin-settings
POST /api/v1/checkin-settings
GET /api/v1/check-reminder

### Duress PIN (3)
GET /api/v1/duress-pin
POST /api/v1/duress-pin
DELETE /api/v1/duress-pin

### Trusted Contacts (5)
POST /api/v1/auth/trusted-contact
GET /api/v1/trusted-contacts
POST /api/v1/trusted-contacts
DELETE /api/v1/trusted-contacts/{id}
GET /api/v1/trusted-contact/notifications/{phone}

### SOS (1)
POST /api/v1/sos

### Incidents (3)
GET /api/v1/incidents
GET /api/v1/incidents/{id}
POST /api/v1/incidents/{id}/resolve

### Location (1)
GET /api/v1/location

### Dashboard (2)
GET /api/v1/dashboard
GET /api/v1/dashboard/activities

### Admin (1)
GET /admin/api/settings

### Health (2)
GET /api/v1/health
GET /api/v1/ping

## COMPLETED SYSTEMS (8)
1. ✅ Authentication
2. ✅ Check-In Engine
3. ✅ SOS Engine
4. ✅ Trusted Contacts
5. ✅ Location Engine
6. ✅ Incident Engine
7. ✅ Notification Engine
8. ✅ Admin Monitoring

## SAFETY FLOWS
1. **Check-In:** Scheduled → Reminder → Check-in → Escalate if missed
2. **SOS:** Trigger → Event → Job → Incident → Notifications
3. **Duress:** Duress PIN → Silent SOS → Notify → Flagged
4. **Incident:** Detected → Created → Escalated → Resolved
5. **Location:** Request → Permission Check → Location Returned

## KNOWN ISSUES
1. ⚠️ Duplicate migrations (6 pairs)
2. ⚠️ Missing model: admin_password_resets
3. ⚠️ No test suite

## LAUNCH READINESS: 75/100
| Category | Score |
|----------|-------|
| Features | 100% |
| API | 100% |
| Database | 85% |
| Frontend | 90% |
| Documentation | 100% |
| Testing | 0% |
| Production | 50% |

## NEXT SPRINT
**Sprint 4 — Testing & QA (2026-06-19 to 2026-06-25)**
