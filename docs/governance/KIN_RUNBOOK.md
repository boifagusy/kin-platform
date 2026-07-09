# KIN Development Runbook

## Start Session

1. cd ~/kin_project
2. ./scripts/ai-bootstrap.sh
3. Read AGENT_LOG.md
4. Verify git status
5. Create full backup
6. Begin discovery

---

## Before Editing

✓ Discovery complete

✓ Execution path verified

✓ Root cause identified

✓ Engineering approval (if required)

---

## After Editing

Run:

Frontend

cd frontend
npm run build

Backend

cd ../backend
php artisan route:list

---

## Runtime

Test on device

Verify feature

Document results

---

## Finish

Update:

PROJECT_STATUS.md

AGENT_LOG.md

Feature docs

Commit

Refresh backup/latest
