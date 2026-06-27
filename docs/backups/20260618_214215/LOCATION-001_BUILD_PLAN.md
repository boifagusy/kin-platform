BRICK: LOCATION-001

STATUS:
BUILD PLAN

OBJECTIVE

Allow trusted contacts to view a user's location
during an active emergency.

────────────────────────────────

EXISTING COMPONENTS

✓ CheckIn coordinates
✓ SOS coordinates
✓ TrustedContact table
✓ User phone field
✓ ActivityLog emergency events

────────────────────────────────

FILES TO CREATE

app/Services/EmergencyPermissionService.php

Purpose:
Determine whether a trusted contact can
access location information.

────────────────────────────────

app/Http/Controllers/Api/V1/LocationController.php

Purpose:
Return emergency location data.

────────────────────────────────

FILES TO MODIFY

routes/api.php

Add:

GET /api/v1/location/{user}

────────────────────────────────

PERMISSION RULE

Location access allowed only when:

Trusted Contact Match
AND
Active Emergency

────────────────────────────────

EMERGENCY RULES

Allow access if:

1. Active SOS
OR
2. CHECKIN_MISSED exists
OR
3. DURESS_PIN_USED exists

────────────────────────────────

LOCATION PRIORITY

1. Active SOS coordinates
2. Latest CheckIn coordinates
3. User last_location

────────────────────────────────

EXPECTED RESPONSE

{
  success: true,
  latitude: x,
  longitude: y,
  maps_url: ...
}

────────────────────────────────

TEST PLAN

TEST-001

Trusted contact
Active SOS

Expected:
200 OK

────────────────────────────────

TEST-002

Trusted contact
No emergency

Expected:
403

────────────────────────────────

TEST-003

Non-trusted contact
Active SOS

Expected:
403

────────────────────────────────

TEST-004

Location exists

Expected:
Google Maps URL generated

────────────────────────────────

ROLLBACK

Delete:

EmergencyPermissionService.php

Delete:

LocationController.php

Remove:

GET /api/v1/location/{user}

────────────────────────────────

STATUS

READY FOR BUILDING
