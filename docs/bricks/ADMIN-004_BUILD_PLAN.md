# ADMIN-004 BUILD PLAN

STATUS:
APPROVED

BRICK:
ADMIN-004

NAME:
Dashboard Real Data

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

GOAL

Replace invalid dashboard metrics.

Use real production data.

Reuse existing services.

Do not create new architecture.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

DISCOVERY EVIDENCE

DashboardController exists.

SafetyMonitorController exists.

SafetyMonitorService exists.

Dashboard widgets exist.

Database contains:

Users: 87
SOS: 11
Escalations: 7
Activities: 54

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

ROOT CAUSE

BUG-001

Active Alerts uses:

SosEvent::where('status','active')

status column does not exist.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

BUG-002

Business Accounts uses:

User::where('role','business')

role column does not exist.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

IMPLEMENTATION STRATEGY

Reuse existing dashboard.

Reuse existing widgets.

Reuse SafetyMonitorService.

Do not create:

- new controller
- new service
- new widgets
- new models

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

PATCHES

PATCH-001

Fix Active Alerts metric.

PATCH-002

Replace Business Accounts metric
with evidence-backed metric.

Recommended:

Active Escalations.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

SIMULATION PLAN

Verify:

Total Users

Active Alerts

Tracked Devices

Active Escalations

Dashboard loads

Empty states

Mobile rendering

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

ROLLBACK

Restore DashboardController backup.

