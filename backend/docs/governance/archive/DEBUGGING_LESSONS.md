# DEBUGGING LESSONS

STATUS:
ACTIVE

PURPOSE:

Store proven root causes.

Prevent repeated mistakes.

Improve future AI debugging accuracy.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

LESSON-001

Issue:

Sidebar link not working.

Actual Root Cause:

Wrong Blade file modified.

Correct file:

resources/views/partials/admin/sidebar.blade.php

Wrong file:

resources/views/admin/partials/sidebar.blade.php

Initial Wrong Assumption:

Navigation logic broken.

Why Assumption Was Wrong:

Rendered layout source was never verified.

Detection Method:

Layout include chain review.

Permanent Prevention:

Before editing Blade files:

1. Inspect layout.
2. Verify include path.
3. Verify rendered component.
4. Then patch.

Confidence:

100%

Status:

PROVEN

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

LESSON-002

Issue:

Duplicate API route.

Observed:

api/v1/sos

api/v1/v1/sos

Actual Root Cause:

Route prefix applied twice.

bootstrap/app.php loaded api routes.

RouteServiceProvider loaded api routes again.

Initial Wrong Assumption:

Duplicate route declaration.

Why Wrong:

Route existed only once.

Framework loaded route file twice.

Detection Method:

Route list inspection.

Permanent Prevention:

Before patching routes:

Check:

bootstrap/app.php

RouteServiceProvider.php

Route registration chain.

Confidence:

100%

Status:

PROVEN

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

LESSON-003

Issue:

AI proposed fix before evidence.

Actual Root Cause:

Insufficient investigation.

Permanent Prevention:

Follow:

EVIDENCE_REQUIREMENTS.md

Patch confidence must exceed 80%.

Below 60%:

STOP

Collect evidence.

Confidence:

100%

Status:

PROVEN

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

TEMPLATE

Issue:

Actual Root Cause:

Initial Wrong Assumption:

Why Assumption Was Wrong:

Detection Method:

Permanent Prevention:

Confidence:

Status:

