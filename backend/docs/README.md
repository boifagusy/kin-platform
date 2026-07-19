# KIN Notification Platform v2.0

## Architecture

```

Business Event → NotificationService → Database → Broadcast (N3) → Clients
→ AutomationService (N9) → Rules → Drivers
→ AnalyticsService (N7) → Dashboard

```

## Bricks

| Brick | Name | Status |
|-------|------|--------|
| N1 | Unified Notification Center | ✅ |
| N2 | In-App Notification Inbox | ✅ |
| N3 | Real-Time Notifications (Reverb) | ✅ |
| N4 | Popup Manager | ✅ |
| N5 | Toast Manager | ✅ |
| N6 | User Notification Preferences | ✅ |
| N7 | Notification Analytics & Management | ✅ |
| N8 | Read/Unread Synchronization | ✅ |
| N9 | Notification Automation & Rules Engine | ✅ |
| N10 | Production Hardening | ✅ |

## API Overview

See `routes/api.php` and `routes/admin.php` for full route list.

Key endpoints:
- `GET /api/v1/notifications` — User notifications
- `GET /api/v1/notifications/unread-count` — Unread count
- `POST /api/v1/notifications/read-all` — Mark all read
- `GET /api/v1/preferences/notifications` — User preferences
- `GET /admin/analytics/notifications` — Admin analytics
- `GET /admin/automation/logs` — Automation execution log
