BRICK: LOCATION-001

STATUS:
VERIFIED

DISCOVERY FINDINGS

Trusted contacts use:

phone matching

NOT

contact_user_id

Schema:

trusted_contacts
- id
- user_id
- name
- phone
- verified
- active

CheckIn coordinates available:

- latitude
- longitude

SOS coordinates available

Emergency events available:

- CHECKIN_MISSED
- DURESS_PIN_USED

EmergencyEscalation model exists.

ARCHITECTURE DECISIONS

Permission:

trusted contact phone match
AND
active emergency

Location priority:

1. SOS
2. CheckIn
3. User.last_location

RISKS

1. Phone normalization required
2. Escalation service not yet implemented
3. Mixed auth architecture

READY FOR:

BUILD PLAN
