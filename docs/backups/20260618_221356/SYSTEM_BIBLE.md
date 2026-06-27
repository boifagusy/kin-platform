# KIN PLATFORM — SYSTEM BIBLE
**Version:** 1.0.0
**Last Updated:** 2026-06-18
**Status:** LIVE

## SYSTEM OVERVIEW
KIN — Personal Safety Platform
- **Backend:** Laravel 12 (PHP 8+)
- **Frontend:** React + Vite
- **Database:** SQLite (Development)
- **Mobile:** Android (Termux)

## FEATURES (18 Complete)
1. Authentication (Login, PIN, Onboarding)
2. Check-In System (Scheduled, Grace Period)
3. Duress PIN (Coercion-resistant)
4. Trusted Contacts (Network of emergency contacts)
5. SOS System (One-tap emergency)
6. Incident Management (Track & resolve)
7. Location Tracking (Real-time)
8. Emergency Escalations (Admin assignment)
9. Admin Dashboard (Users, logs, settings)
10. Notifications (SMS, Push)

## DATABASE SCHEMA (26 Tables)
| Table | Model | Status |
|-------|-------|--------|
| users | User.php | ✅ |
| check_ins | CheckIn.php | ✅ |
| checkin_settings | CheckinSetting.php | ✅ |
| sos_events | SosEvent.php | ✅ |
| assistance_requests | AssistanceRequest.php | ✅ |
| trusted_contacts | TrustedContact.php | ✅ |
| safety_incidents | SafetyIncident.php | ✅ |
| incident_notifications | IncidentNotification.php | ✅ |
| emergency_escalations | EmergencyEscalation.php | ✅ |
| activity_logs | ActivityLog.php | ✅ |
| password_resets | PasswordReset.php | ✅ |
| admin_users | AdminUser.php | ✅ |
| admin_logs | AdminLog.php | ✅ |
| alert_notes | AlertNote.php | ✅ |
| system_settings | SystemSetting.php | ✅ |
| user_statuses | UserStatus.php | ✅ |
| password_reset_tokens | - | Framework |
| personal_access_tokens | - | Framework |
| migrations | - | Framework |
| cache | - | Framework |
| jobs | - | Framework |
| sessions | - | Framework |

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

## FRONTEND SCREENS (10+)
- Auth (Login, PIN login)
- Onboarding (User onboarding)
- Dashboard (Main dashboard)
- CheckIn (Check-in status)
- Settings (User settings)
- Profile (User profile)
- Map (Location map)
- Alerts (Alert history)
- Network (Trusted contacts)
- UI Polish (UI enhancements)

## SAFETY FLOWS
1. **Check-In:** Scheduled → Reminder → User checks in (safe/assistance/emergency) → Escalate if missed
2. **SOS:** Trigger → Event created → Is Duress? (silent/visible) → Notify contacts → Admin escalation
3. **Duress PIN:** Enter duress PIN → Silent SOS → Notify contacts → Flagged as duress
4. **Trusted Contact:** Add → Verify → Active → Notifications enabled
5. **Incident:** Detected → Created → Notified → Escalated → Resolved

## DEPLOYMENT STATUS
- Development: ✅ Active
- Staging: ❌ Not Configured
- Production: ❌ Not Configured

## ISSUES FOUND
1. Duplicate migrations (6 pairs) — needs cleanup
2. Missing model for admin_password_resets
3. No test suite
4. SQLite not suitable for production

## NEXT STEPS
1. Clean up duplicate migrations
2. Create AdminPasswordReset model
3. Implement test suite
4. Migrate to PostgreSQL

