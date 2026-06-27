# CODEBASE DISCOVERY

STATUS:
FROZEN

PURPOSE

Before modifying code, collect evidence of where code lives.

Never assume ownership.

Never assume architecture.

The codebase is the source of truth.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

DISCOVERY RULE

Before:

- New feature
- Refactor
- Debugging
- Bug fixing
- Database change
- Architecture change

Collect discovery evidence.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

CONTROLLERS

Run:

find app/Http/Controllers -type f | sort

Purpose:

Discover request entry points.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

SERVICES

Run:

find app/Services -type f | sort

Purpose:

Discover business logic.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

MODELS

Run:

find app/Models -type f | sort

Purpose:

Discover data ownership.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

ROUTES

Run:

cat routes/web.php

cat routes/api.php

Purpose:

Discover request flow.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

VIEWS

Run:

find resources/views -type f | sort

Purpose:

Discover UI ownership.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

JOBS

Run:

find app/Jobs -type f | sort

Purpose:

Discover background processing.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

EVENTS

Run:

find app/Events -type f | sort

Purpose:

Discover event architecture.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

LISTENERS

Run:

find app/Listeners -type f | sort

Purpose:

Discover automation flow.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

DATABASE SCHEMA

Run:

php artisan tinker --execute="
print_r(Schema::getColumnListing('TABLE_NAME'));
"

Purpose:

Verify actual database structure.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

DEBUGGING DISCOVERY

Before debugging collect:

[ ] Routes

[ ] Controller

[ ] Service

[ ] Model

[ ] View

[ ] Database schema

[ ] Logs

[ ] Screenshots

[ ] Reproduction steps

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

FILE OWNERSHIP RULE

Before editing a file:

Prove:

1. The file is used

2. The file owns the feature

3. The file is loaded

Example:

Wrong:

admin/partials/sidebar.blade.php

Correct:

partials/admin/sidebar.blade.php

Verify before patching.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

TERMUX RULE

Always request:

Copy-paste commands.

Never require desktop tools.

Never require IDE access.

Prefer:

cat

grep

find

sed

php artisan

over manual inspection.

