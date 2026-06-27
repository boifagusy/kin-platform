# KIN ARCHITECTURE EVIDENCE

Generated:
2026-06-14

## Controllers

Admin:
- AuthController
- DashboardController
- SafetyMonitorController
- UserManagementController
- AuditController
- SystemSettingsController

API:
- AuthController
- DashboardController
- CheckInController
- AssistanceController
- SosController
- CheckInSettingsController
- HealthController
- ReminderController
- DuressPinController
- TrustedContactController
- ActivitiesController
- LocationController

## Services

Admin:
- AuditService
- PermissionService
- SafetyMonitorService
- SystemSettingsService
- UserActionService
- UserManagementService

Core:
- CheckInService
- DashboardSnapshotService
- EmergencyPermissionService
- NotificationService
- PasswordResetService
- SafetyScoreService

## Events

- CheckInCompleted
- SOSTriggered

## Jobs

- ProcessMissedCheckInJob
- SendCheckInReminderJob
- SendSosAlertJob

## Listeners

- CreateActivityLog
- QueueSosAlert
- RefreshDashboardCache
- UpdateSafetyScore

## Core Models

- User
- UserStatus
- TrustedContact
- CheckIn
- CheckinSetting
- SosEvent
- EmergencyEscalation
- ActivityLog
- AlertNote
- AssistanceRequest
- PasswordReset
- AdminUser
- AdminLog
- SystemSetting

