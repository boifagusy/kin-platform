# KIN PLATFORM — COMPLETE CODEMAP

Updated: 2026-06-22 | Version: 3.0

---

## BACKEND STRUCTURE

~/storage/kin_platform/backend/
├── app/
│   ├── Models/                              # 19 Models
│   │   ├── User.php
│   │   ├── TrustedContact.php               # USES 'phone' NOT 'contact_phone'
│   │   ├── SafetyIncident.php
│   │   ├── IncidentNotification.php
│   │   ├── EmergencyEscalation.php
│   │   ├── SosEvent.php
│   │   ├── CheckIn.php
│   │   ├── CheckinSetting.php
│   │   ├── ActivityLog.php
│   │   ├── AlertNote.php
│   │   ├── AssistanceRequest.php
│   │   ├── PasswordReset.php
│   │   ├── SystemSetting.php
│   │   ├── UserStatus.php
│   │   ├── AdminUser.php
│   │   └── AdminLog.php
│   ├── Http/Controllers/Api/V1/             # 15 API Controllers
│   │   ├── AuthController.php
│   │   ├── SosController.php
│   │   ├── IncidentController.php
│   │   ├── TrustedContactController.php
│   │   ├── CheckInController.php
│   │   ├── CheckInSettingsController.php
│   │   ├── DashboardController.php
│   │   ├── DuressPinController.php
│   │   ├── PasswordResetController.php
│   │   ├── LocationController.php
│   │   ├── ActivitiesController.php
│   │   ├── AssistanceController.php
│   │   ├── HealthController.php
│   │   ├── ReminderController.php
│   │   └── UserController.php
│   ├── Services/                            # 12+ Services
│   │   ├── NotificationService.php
│   │   ├── EmergencyPermissionService.php
│   │   ├── PasswordResetService.php
│   │   ├── CheckInService.php
│   │   ├── DashboardSnapshotService.php
│   │   └── SafetyScoreService.php
│   ├── Events/                              # System Events
│   │   ├── SOSTriggered.php
│   │   └── CheckInCompleted.php
│   ├── Listeners/                           # Event Listeners
│   │   ├── QueueSosAlert.php
│   │   ├── CreateActivityLog.php
│   │   ├── RefreshDashboardCache.php
│   │   └── UpdateSafetyScore.php
│   └── Jobs/                                # Queue Jobs
│       ├── SendSosAlertJob.php
│       ├── ProcessMissedCheckInJob.php
│       └── SendCheckInReminderJob.php
├── routes/
│   └── api.php                              # 33+ API Endpoints
├── database/migrations/                      # 35+ Migrations
├── storage/logs/laravel.log                 # Main log file
└── .env                                     # Environment config

---

## FRONTEND STRUCTURE

~/storage/kin_platform/frontend/
├── src/
│   ├── screens/                             # 10+ Screens
│   │   ├── auth/
│   │   │   ├── PhoneEntryScreen.jsx
│   │   │   ├── CreatePinScreen.jsx
│   │   │   ├── LoginPinScreen.jsx
│   │   │   ├── UserDetailsScreen.jsx
│   │   │   └── TrustedContactScreen.jsx
│   │   ├── dashboard/
│   │   │   └── DashboardScreen.jsx
│   │   ├── settings/
│   │   │   ├── CheckInSettingsScreen.jsx
│   │   │   └── DuressPinSetupScreen.jsx
│   │   └── ui-polish/                      # PRODUCTION SCREENS
│   │       ├── DashboardScreenV2.jsx       # MAIN DASHBOARD
│   │       ├── DuressPinSetupScreenV2.jsx
│   │       ├── LoginPinScreenV2.jsx
│   │       ├── CreatePinScreenV2.jsx
│   │       ├── PhoneEntryScreenV2.jsx
│   │       ├── UserDetailsScreenV2.jsx
│   │       └── TrustedContactScreenV2.jsx
│   ├── components/                          # 40+ Components
│   │   └── dashboard/
│   │       ├── HeaderV2.jsx
│   │       ├── SafetyScoreCardMinimal.jsx
│   │       ├── TrustedContactCard.jsx
│   │       ├── SafetyCheckCard.jsx
│   │       ├── AssistanceOptions.jsx
│   │       ├── ActivityTimeline.jsx
│   │       ├── SetupCard.jsx
│   │       ├── EmergencyModal.jsx
│   │       └── BottomNav.jsx
│   ├── services/                            # 7 Services
│   │   ├── api.js                           # API Client
│   │   ├── notificationService.js           # Local notifications
│   │   ├── BackgroundLocationService.js     # Location tracking
│   │   ├── locationTracker.js
│   │   ├── offlineQueueService.js
│   │   ├── onboardingDraftService.js
│   │   └── onboardingStorage.js
│   ├── utils/
│   │   └── location.js
│   ├── contexts/
│   │   └── AuthContext.jsx
│   ├── App.jsx
│   └── router.jsx
├── android/                                 # Capacitor Android
│   ├── app/
│   │   ├── build.gradle                     # compileSdk 36, Java 21
│   │   └── src/main/
│   │       ├── AndroidManifest.xml          # Permissions
│   │       └── java/com/kin/app/
│   ├── build.gradle                         # AGP 8.9.1
│   ├── gradle.properties
│   └── local.properties
├── capacitor.config.json
└── dist/

---

## CRITICAL FILES (NEVER MODIFY WITHOUT BACKUP)

FILE                                              WHY CRITICAL
-------------------------------------------------- --------------------------------------------
app/Models/TrustedContact.php                     Uses 'phone' column - SOS depends on it
app/Http/Controllers/Api/V1/SosController.php     Core SOS logic
frontend/src/screens/ui-polish/DashboardScreenV2.jsx  Main production screen
frontend/src/services/notificationService.js      Notification permissions
routes/api.php                                     All API routing
frontend/android/app/build.gradle                  Android build config
frontend/android/app/src/main/AndroidManifest.xml  Android permissions
.env                                               Environment variables

---

## SOS FLOW (FULL CALL CHAIN)

FRONTEND:
DashboardScreenV2.jsx → handleSOS() → api.sos.trigger()
api.js → POST /api/v1/sos

BACKEND:
routes/api.php → Route::post('/sos', [SosController::class, 'store'])
SosController.php::store() → creates SosEvent → dispatches SOSTriggered
SOSTriggered.php (Event) → contains user_id, location, is_duress
QueueSosAlert.php (Listener) → dispatches SendSosAlertJob
SendSosAlertJob.php (Job) → creates EmergencyEscalation
→ creates SafetyIncident
→ looks up TrustedContacts (by phone)
→ creates IncidentNotification for each contact
→ sends notifications

MODELS INVOLVED:
EmergencyEscalation.php
SafetyIncident.php
IncidentNotification.php
TrustedContact.php (USES 'phone')

---

## AUTH FLOW

FRONTEND:
PhoneEntryScreenV2.jsx → api.auth.login()
api.js → POST /api/v1/auth/login
CreatePinScreenV2.jsx / LoginPinScreenV2.jsx → api.auth.createPin() / api.auth.loginPin()
AuthContext.jsx → login() / logout() / checkAuth()

BACKEND:
routes/api.php → POST /auth/login → AuthController@login
→ POST /auth/create-pin → AuthController@createPin
→ POST /auth/login-pin → AuthController@loginPin

AuthController.php:
login() → sends OTP
confirmPhone() → verifies OTP
createPin() → hashes and stores PIN
loginPin() → verifies PIN and creates session

MODELS:
User.php (login_pin_hash, duress_pin_hash)
TrustedContact.php (phone, verification_token)

---

## TRUSTED CONTACT FLOW

FRONTEND:
TrustedContactScreenV2.jsx → handleAdd() → api.trustedContacts.store()
api.js → POST /api/v1/trusted-contacts

BACKEND:
routes/api.php:
GET /trusted-contacts → TrustedContactController@index
POST /trusted-contacts → TrustedContactController@store
DELETE /trusted-contacts/{id} → TrustedContactController@destroy
GET /trusted-contact/verify/{token} → TrustedContactController@verify

TrustedContactController.php:
index() → lists user's contacts
store() → validates phone, creates contact, sends verification
destroy() → removes contact
verify() → verifies contact (sets verified=true)

MODEL:
TrustedContact.php (USES 'phone' column)

---

## CHECK-IN FLOW

FRONTEND:
DashboardScreenV2.jsx → handleCheckIn() → api.checkin.store()
CheckInSettingsScreen.jsx → api.checkinSettings.update()

BACKEND:
routes/api.php:
POST /checkin → CheckInController@store
GET /checkin-settings → CheckInSettingsController@get
POST /checkin-settings → CheckInSettingsController@update

CheckInController.php:
store() → creates CheckIn record → dispatches CheckInCompleted

CheckInSettingsController.php:
get() → returns user settings
update() → updates settings (time, frequency)

MODELS:
CheckIn.php
CheckinSetting.php

---

## DEBUGGING GUIDE

ISSUE TYPE              WHERE TO CHECK                        WHAT TO LOOK FOR
------------------------ ------------------------------------ --------------------------------
API not working         routes/api.php                        Route exists? Correct URL?
                        AuthController.php                    Method exists? Validations?
                        .env                                  API_URL correct?
                        storage/logs/laravel.log              Error messages

Database error          database/migrations/                  Table exists? Columns correct?
                        app/Models/                           Model relationships correct?
                        php artisan migrate:status            All migrations run?

Frontend not loading    router.jsx                            Route defined?
                        screens/ui-polish/                    Screen exists?
                        services/api.js                       API base URL correct?
                        Browser Console                       JavaScript errors

SOS not working         SosController.php                     Validations pass?
                        SOSTriggered.php                      Event dispatched?
                        QueueSosAlert.php                     Listener registered?
                        SendSosAlertJob.php                   Job processes correctly?
                        TrustedContact.php                    Uses 'phone' column?

Notifications not       notificationService.js                Permissions granted?
sending                 AndroidManifest.xml                   Permissions declared?
                        LocalNotifications                    Plugin installed?

Location not working    BackgroundLocationService.js          Started correctly?
                        AndroidManifest.xml                   Location permissions?
                        location.js                           GPS enabled?

Android build fails     app/build.gradle                      compileSdk, minSdk correct?
                        gradle.properties                     JVM args correct?
                        local.properties                      SDK path correct?
                        Build logs                            Error details

---

## API ENDPOINTS QUICK REFERENCE

AUTH (10):
POST /api/v1/auth/login
POST /api/v1/auth/confirm-phone
POST /api/v1/auth/create-pin
POST /api/v1/auth/login-pin
POST /api/v1/auth/user-details
POST /api/v1/auth/trusted-contact
POST /api/v1/auth/complete-onboarding
POST /api/v1/forgot-pin/send-otp
POST /api/v1/forgot-pin/verify-otp
POST /api/v1/forgot-pin/reset

TRUSTED CONTACTS (4):
GET    /api/v1/trusted-contacts
POST   /api/v1/trusted-contacts
DELETE /api/v1/trusted-contacts/{id}
GET    /api/v1/trusted-contact/verify/{token}
GET    /api/v1/trusted-contact/notifications/{phone}

SOS & INCIDENTS (4):
POST /api/v1/sos
GET  /api/v1/incidents
GET  /api/v1/incidents/{id}
POST /api/v1/incidents/{id}/resolve

CHECK-IN (3):
POST /api/v1/checkin
GET  /api/v1/checkin-settings
POST /api/v1/checkin-settings

DASHBOARD (8):
GET  /api/v1/dashboard
GET  /api/v1/dashboard/activities
GET  /api/v1/duress-pin
POST /api/v1/duress-pin
DELETE /api/v1/duress-pin
GET  /api/v1/health
GET  /api/v1/ping
GET  /api/v1/check-reminder

---

## QUICK SEARCH REFERENCE

WHAT                           WHERE
------------------------------- ------------------------------------------------
User model                     backend/app/Models/User.php
TrustedContact model           backend/app/Models/TrustedContact.php
SOS Controller                 backend/app/Http/Controllers/Api/V1/SosController.php
SOS Event                      backend/app/Events/SOSTriggered.php
SOS Job                        backend/app/Jobs/SendSosAlertJob.php
API Routes                     backend/routes/api.php
Main Dashboard                 frontend/src/screens/ui-polish/DashboardScreenV2.jsx
API Client                     frontend/src/services/api.js
Notifications                  frontend/src/services/notificationService.js
Capacitor Config               frontend/capacitor.config.json
Android Build                  frontend/android/app/build.gradle
Android Permissions            frontend/android/app/src/main/AndroidManifest.xml
Laravel Logs                   backend/storage/logs/laravel.log
Build Logs                     docs/build_output_*.log

---

## UPDATE WORKFLOWS

Adding New API Endpoint:
1. Define route in routes/api.php
2. Create/Update Controller method
3. Update Model if needed
4. Test with curl/Postman
5. Update SYSTEM_BIBLE.md
6. Update frontend api.js

Adding New Screen:
1. Create .jsx file in screens/
2. Add route in router.jsx
3. Add navigation link
4. Test routing
5. Update STATUS.md

Adding New Model:
1. Create migration (php artisan make:migration)
2. Create Model in app/Models/
3. Define relationships
4. Run migration (php artisan migrate)
5. Update SYSTEM_BIBLE.md
6. Update CODEMAP.md

Fixing Bug:
1. Identify bug (check logs)
2. Find root cause
3. Create backup
4. Fix bug
5. Test thoroughly
6. Update STATUS.md

---

## CRITICAL COMMANDS

# Set Java 21
export JAVA_HOME=/data/data/com.termux/files/usr/lib/jvm/java-21-openjdk
export PATH=$JAVA_HOME/bin:$PATH

# Set Android SDK
export ANDROID_HOME=/data/data/com.termux/files/usr/lib/android-sdk
export PATH=$ANDROID_HOME/cmdline-tools/latest/bin:$PATH
export PATH=$ANDROID_HOME/platform-tools:$PATH

# Build React
cd ~/storage/kin_platform/frontend
npm run build

# Sync Capacitor
npx cap sync android

# Build APK
npx cap build android

# Check Laravel logs
tail -f ~/storage/kin_platform/backend/storage/logs/laravel.log

# List API routes
cd ~/storage/kin_platform/backend
php artisan route:list | grep api

# Check migrations
php artisan migrate:status

# Test API
curl http://127.0.0.1:8000/api/v1/health

---

## BACKUP COMMAND

cp file file.backup_$(date +%Y%m%d_%H%M%S)

---

Use this CODEMAP to navigate, update, and debug the KIN platform.
