BRICK: FIX-002

Title:
Trusted Contact Onboarding Persistence

Status:
READY FOR IMPLEMENTATION

Evidence:

TrustedContact Count = 2

Only creator:
TrustedContactController::store()

SaveTrustedContactAction:
Contains TODO
Does not write database records

Architecture:

Reuse:
- trusted_contacts table
- TrustedContact model

Do Not Create:
- New tables
- New routes
- New controllers

Files To Modify:

app/Actions/Auth/SaveTrustedContactAction.php

Verification:

Before:
TrustedContact::count() = 2

After onboarding test:
TrustedContact::count() = 3

Risk:
LOW

Priority:
P0
