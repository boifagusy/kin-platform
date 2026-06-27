# PRODUCTION SIMULATION

STATUS:
FROZEN

PURPOSE

Prevent bricks from being marked tested without realistic verification.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

RULE

A brick is NOT tested because:

- Code compiles
- Page loads
- API returns 200
- AI says "working"

A brick is tested only after simulation.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

SIMULATION PHASE

Required before:

VALIDATED

Required before:

FROZEN

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

SIMULATION CHECKLIST

Backend

[ ] Route exists

[ ] Controller executes

[ ] Service executes

[ ] Database writes correctly

[ ] Database reads correctly

[ ] Validation works

[ ] Errors handled

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Frontend

[ ] Page loads

[ ] Buttons work

[ ] Forms submit

[ ] Errors display

[ ] Success flow works

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Failure Testing

[ ] Invalid input

[ ] Missing data

[ ] Unauthorized request

[ ] Empty result set

[ ] Database failure scenario reviewed

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Production Simulation

Simulate:

Normal User

Power User

Bad Input

Empty Database

Maximum Expected Usage

Document results.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

REAL DEVICE TEST

AI testing is insufficient.

Require:

[ ] Android test

[ ] Mobile browser test

[ ] Actual user interaction

[ ] Screenshot evidence

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

ROOT CAUSE RULE

If simulation fails:

STOP

Collect evidence.

Do not patch blindly.

Return to:

DISCOVERING

VERIFIED

ROOT CAUSE

PATCH

SIMULATION

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

VALIDATION REQUIREMENTS

Before VALIDATED:

Provide:

- Test steps
- Test results
- Screenshots
- Known limitations

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

FROZEN REQUIREMENTS

Only user may move:

VALIDATED
↓
FROZEN

