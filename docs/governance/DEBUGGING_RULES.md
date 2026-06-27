DEBUGGING & DEVELOPMENT GOVERNANCE RULES (MANDATORY)

When debugging, building, refactoring, or extending any system, follow these rules exactly.

──────────────────────────────

RULE 1 — NEVER GUESS

Do not assume the cause.

Always collect evidence first.

Required evidence:

• Error message
• Stack trace
• Console output
• Laravel log
• Route output
• Database output
• Network response

No code changes until evidence is collected.

──────────────────────────────

RULE 2 — IDENTIFY ROOT CAUSE FIRST

Before writing code, answer:

What is broken?

Why is it broken?

Which file causes it?

Which function causes it?

Which database table causes it?

Do not fix symptoms.

Fix root cause only.

──────────────────────────────

RULE 3 — SHOW INVESTIGATION PLAN

Always provide:

STEP 1
Gather evidence

STEP 2
Identify root cause

STEP 3
Verify root cause

STEP 4
Apply fix

STEP 5
Retest

STEP 6
Cleanup

No coding before investigation.

──────────────────────────────

RULE 4 — ONE CHANGE AT A TIME

Never modify:

Controllers
Services
Views
Routes
Database

all at once.

Only change one layer.

Test.

Then continue.

──────────────────────────────

RULE 5 — REUSE BEFORE CREATE

Before creating:

New table
New API
New service
New controller
New component

Check if the system already has one.

Always answer:

Can we reuse an existing brick?

Decision order:

REUSE
↓
EXTEND
↓
CREATE

──────────────────────────────

RULE 6 — ALWAYS SHOW COMMANDS

Primary environment: Termux.

Every debugging step must include:

Exact commands

Examples:

php artisan route:list

php artisan optimize:clear

tail -50 storage/logs/laravel.log

No assumptions.

──────────────────────────────

RULE 7 — VERIFY BEFORE FIX

Always verify:

Route exists

Controller exists

Method exists

View exists

Database column exists

Model exists

before changing code.

──────────────────────────────

RULE 8 — DO NOT REWRITE WORKING FILES

If a file is working:

DO NOT regenerate it.

Only patch affected section.

Prefer minimal diffs.

──────────────────────────────

RULE 9 — DEBUG REPORT REQUIRED

After every fix provide:

Issue

Root Cause

Files Changed

Commands Run

Result

Remaining Risks

──────────────────────────────

RULE 10 — CLEANUP REQUIRED

After successful fix:

Identify

Test files

Debug routes

Temporary controllers

Unused migrations

Unused views

Unused services

and provide removal commands.

──────────────────────────────

RULE 11 — LEGO ARCHITECTURE RULE

Every fix must follow:

REUSE
↓
EXTEND
↓
CREATE

Never create new architecture when existing architecture can be extended.

──────────────────────────────

RULE 12 — STOP AFTER ROOT CAUSE

Once root cause is identified:

STOP

Ask for approval before:

Major architectural changes

Database redesign

Large refactors

New subsystems

Do not automatically refactor.

──────────────────────────────

RULE 13 — PERFORMANCE CHECK

Before adding code answer:

Will this increase:

Database queries?

Memory usage?

API calls?

Server load?

If yes:

Provide cheaper alternatives.

──────────────────────────────

RULE 14 — NO FULL FILE REWRITE

Default behavior:

Patch only.

Do not regenerate entire files unless explicitly requested.

──────────────────────────────

RULE 15 — PRODUCTION SAFETY

Before migration or database change:

Show impact analysis.

Show rollback plan.

Show backup commands.

Never modify production data blindly.

──────────────────────────────

RULE 16 — VERIFY CURRENT DIRECTORY

Before running any command:

pwd

Expected locations:

Laravel:
~/storage/project/backend

Frontend:
~/storage/project/frontend

Documentation:
~/storage/project/docs

If command fails with:

Could not open input file: artisan

Then:

1. Run pwd
2. Verify directory
3. Navigate to backend
4. Retry

Never debug application code before confirming location.

──────────────────────────────

RULE 17 — EVIDENCE BEFORE ARCHITECTURE

Before proposing any feature:

Search first.

Routes

Controllers

Services

Models

Frontend screens

Database migrations

Commands:

php artisan route:list

grep -rn "keyword" app/

grep -rn "keyword" routes/

find src -iname "keyword"

Determine:

Already exists?

Partially exists?

Broken?

Missing?

Never build what already exists.

──────────────────────────────

RULE 18 — BUILD LOG REQUIRED

Every completed change must generate:

BRICK ID

Date

Goal

Files Created

Files Modified

Database Changes

Routes Added

Commands Executed

Test Results

Known Risks

Status

No undocumented changes.

──────────────────────────────

RULE 19 — SYSTEM BIBLE UPDATE REQUIRED

When architecture changes:

Update documentation immediately.

Do not rely on memory.

Documentation becomes source of truth.

Code and documentation must stay synchronized.

──────────────────────────────

RULE 20 — LONG-TERM THINKING

Every decision must answer:

Will this still work with:

10 users?

1,000 users?

100,000 users?

Can it scale?

Can it be maintained?

Can another developer understand it?

Prefer long-term architecture over short-term shortcuts.

──────────────────────────────

RULE 21 — AI VALIDATION RULE

Any AI-generated code must be validated.

Verify:

Routes

Database columns

Model relationships

Method names

File paths

Existing architecture

AI suggestions are hypotheses until verified.

──────────────────────────────

RULE 22 — TERMUX FIRST

Instructions must always include:

Exact directory

Exact command

Expected output

Verification command

Rollback command (if applicable)

Assume beginner-friendly execution.

──────────────────────────────

RULE 23 — NO HIDDEN ASSUMPTIONS

Every recommendation must clearly state:

Known facts

Unknown facts

Assumptions

Verification steps

Never present assumptions as facts.

──────────────────────────────

RULE 24 — SAFETY BEFORE FEATURES

Priority order:

Data Integrity
↓
Security
↓
Performance
↓
Maintainability
↓
Features
↓
Nice-to-have Enhancements

Never sacrifice system integrity for speed.

──────────────────────────────

RULE 25 — DOCUMENTATION IS A FEATURE

A feature is not complete until:

Code exists

Tests pass

Documentation updated

Build log written

Cleanup performed

Only then is the brick considered complete.

