KIN SYSTEM BIBLE

Product Vision

KIN is an Autonomous Personal Safety Operating System.

User checks in
↓
KIN monitors
↓
KIN detects risk
↓
KIN escalates
↓
KIN protects

---

Core Rules

Location Privacy

KIN is NOT a tracking app.

Location Visible:

- SOS
- Missed Check-In
- Duress PIN

Location Hidden:

- Normal operation
- Curiosity
- Continuous monitoring

---

Trusted Contacts

MVP Rules

Maximum Contacts:
1

Relationship Type:
One-way

Permissions:

Can:

- Receive alerts
- View emergency location
- Open Google Maps

Cannot:

- Change settings
- Mark user safe
- Modify account

---

Safety Events

CHECKIN_SAFE

CHECKIN_REMINDER_SENT

CHECKIN_ALERT_SENT

CHECKIN_MISSED

DURESS_PIN_USED

SOS_TRIGGERED

---

Existing Tables

users

check_ins

activity_logs

trusted_contacts

sos_events

assistance_requests

emergency_escalations

checkin_settings

---

Existing APIs

POST /api/v1/checkin

POST /api/v1/sos

POST /api/v1/assistance

GET /api/v1/dashboard

GET /api/v1/health

GET /api/v1/ping

GET /api/v1/trusted-contacts

POST /api/v1/trusted-contacts

DELETE /api/v1/trusted-contacts/{id}
