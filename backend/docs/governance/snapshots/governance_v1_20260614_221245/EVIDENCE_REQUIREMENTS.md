# EVIDENCE REQUIREMENTS

STATUS:
FROZEN

PURPOSE

Prevent AI from coding before understanding the system.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

NO IMPLEMENTATION BEFORE EVIDENCE

Before:

- Coding
- Refactoring
- Debugging
- Database Changes
- Architecture Changes

Evidence must be collected.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

MINIMUM EVIDENCE

Controllers

Services

Models

Routes

Views

Database Schema

Logs

API Responses

Related Governance Documents

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

DISCOVERY CHECKLIST

[ ] Route file reviewed

[ ] Controller reviewed

[ ] Service reviewed

[ ] Model reviewed

[ ] View reviewed

[ ] Database schema reviewed

[ ] Existing tests reviewed

[ ] Existing brick reviewed

[ ] Reusable blocks reviewed

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

DEBUGGING EVIDENCE

Before fixing bugs collect:

[ ] Error message

[ ] Logs

[ ] Route evidence

[ ] Controller evidence

[ ] Service evidence

[ ] Model evidence

[ ] View evidence

[ ] Reproduction steps

[ ] Screenshots if available

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

ROOT CAUSE RULE

Never patch symptoms.

Identify:

What broke?

Why?

Where?

Which file?

Which function?

Which table?

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

PATCH CONFIDENCE

95-100%
Root cause proven

80-94%
Strong evidence

60-79%
Likely

Below 60%

STOP

Collect more evidence.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

AI FAILURE DETECTION

If AI proposes:

- Random JS
- Random CSS
- Full rewrites
- New architecture
- New tables
- New services

before evidence,

mark:

AI ERROR:
Insufficient Evidence

Return to discovery phase.

