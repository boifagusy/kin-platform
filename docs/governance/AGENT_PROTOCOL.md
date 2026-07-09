# KIN AGENT OPERATING PROTOCOL

Version: 2.2
Status: LOCKED
Effective Date: 2026-07-09

---

## PURPOSE

This protocol defines how every AI agent must work on the KIN Platform.

Its goals are to:

- Prevent accidental regressions
- Prevent duplicate work
- Preserve architecture
- Ensure every change is verified
- Coordinate multiple AI sessions
- Produce production-ready software

---

# 1. ENGINEERING PRINCIPLE

Evidence beats assumptions.

Never claim:

- Build passed
- Fixed
- Production ready
- Tested
- Should work

unless terminal output proving it is included.

If no verification has been run, state:

NOT VERIFIED — I have not run this.

---

# 2. SESSION START

Every AI session begins with:

1. Read AGENT_LOG.md
2. Check git status
3. Verify no active edit session
4. Perform Discovery
5. Create full backup

---

# 3. DISCOVERY

Every implementation begins with:

Discovery Summary

File being edited

Execution path

Dependencies

Last commits

Git status

Never modify files before discovery.

---

# 4. ROOT CAUSE

Never fix symptoms.

Always determine:

What broke?

Why?

Which file?

Which function?

Which service?

Which API?

Only then implement.

---

# 5. IMPLEMENTATION WORKFLOW

Discovery

↓

Backup

↓

Patch

↓

Verify

↓

Build

↓

Runtime Test

↓

Documentation

↓

Commit

↓

Refresh Backup

No steps may be skipped.

---

# 6. BACKUPS

Use:

backup/latest

for current verified project.

Use:

backup/releases

for milestone snapshots.

Do not create:

*.backup

*.bak

timestamped copies

Git is the primary rollback mechanism.

---

# 7. PATCHING

Preferred:

Python

Brace counting

AST

Targeted replacements

Avoid:

Large rewrites

Whitespace-sensitive search/replace

Whole-file regeneration unless requested.

---

# 8. VERIFICATION

Frontend

cd frontend
npm run build

Backend

cd backend
php artisan route:list

Paste terminal output.

Include exit code.

No exceptions.

---

# 9. RUNTIME TEST

Build success does not equal working software.

Verify:

Navigation

API

Buttons

Forms

Device behavior

User flow

---

# 10. DOCUMENTATION

Update when required:

PROJECT_STATUS.md

AGENT_LOG.md

BUILD_LOG.md

Relevant governance docs

Documentation is part of the feature.

---

# 11. CHANGE REPORT

Every completed task includes:

Objective

Root Cause

Files Modified

Verification

Commit

Rollback

Outstanding Issues

---

# 12. MULTI-AGENT RULES

Only one active writer per file.

Always read:

docs/governance/AGENT_LOG.md

before editing.

Respect locked files.

---

# 13. ENGINEERING MANAGER

The Engineering Manager decides:

Priorities

Risk approval

Production readiness

Commits

The AI is an implementation engineer.

---

END OF PROTOCOL

Version 2.2
