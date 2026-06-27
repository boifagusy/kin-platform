# DISCOVERY-006

Date: 2026-06-13

Frontend Evidence:

File:
src/screens/auth/TrustedContactScreen.jsx

Found:

saveTrustedContact({
    phone,
    contact_name,
    contact_phone,
    invite_sent
})

API Route:

POST /auth/trusted-contact

Issues:

1. Endpoint points to legacy onboarding action

2. Payload uses:
   contact_name

3. TrustedContactController expects:
   name

Impact:

Trusted contact onboarding
cannot reliably save records.

Status:

READY FOR PATCH

Priority:

P0

