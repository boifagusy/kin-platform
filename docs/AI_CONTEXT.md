# AI CONTEXT

## Core Principles

- Discovery First
- Evidence First
- Read Before Edit
- Never Guess
- Always Verify

---

## Documentation Rules

Git is the source of history.

Never create duplicate documentation.

Update the living document.

History belongs in Git.

---

## Storage Strategy

Auth → localStorage

Preferences → localStorage

Offline Queue → IndexedDB

Location History → IndexedDB

---

## Reporting Standard

1 Session Start

2 Pre-flight

3 Discovery

4 Root Cause

5 Modification Report

6 Implementation

7 Build

8 Runtime Verification

9 Session End

---

## Build Commands

Frontend

npm run build

Backend

php artisan test

php artisan migrate --pretend

---

## Never

Never modify protected files without checking PROTECTED_FILES.md

Never request files before repository search

Never skip Discovery


## Current State

**Last Updated:** 2026-07-08

### Completed
- ✅ Blocks 3A.1-3A.5 — Location Queue Infrastructure
- ✅ Persistence verified via IndexedDB
- ✅ Build passes (npm run build exit 0)

### Next Priority
- Block 3B — Location History
- OR Integration with SOS/Check-in flow

### Active Branch
- recovery/auth-integration

### Build Status
- ✅ Passing

### Known Issues
- None blocking
