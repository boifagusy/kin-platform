# KIN PLATFORM — DEBUGGING LESSONS LEARNED
# Last Updated: 2026-06-15

---

## LESSON-001: Dashboard Metrics Inaccurate

**Brick:** ADMIN-004
**Date:** 2026-06-15
**Severity:** Medium

**Issue:**
Dashboard displayed placeholder or incorrect metrics instead of real database values.

**Root Cause:**
1. Queries did not match actual database schema
2. `SosEvent::where('status', 'active')` assumed a `status` column that doesn't exist
3. `User::where('role', 'business')` assumed a `role` column that doesn't exist
4. No verification of column existence before writing queries

**Detection Method:**
- Direct database comparison against dashboard displayed values
- Schema inspection using `Schema::getColumnListing()`

**Fix Applied:**
1. Used `SosEvent::whereNull('resolved_at')` for active SOS count
2. Used `EmergencyEscalation::where('status', 'active')` for active alerts
3. Used `User::whereHas('checkIns')` for tracked devices

**Prevention:**
- Always verify schema before writing database queries
- Use `php artisan tinker` to test queries against production data
- Compare dashboard output with direct database queries
- Follow BACKUP → PATCH → VERIFY → ROLLBACK workflow

**Related Rules:**
- EVIDENCE_REQUIREMENTS.md
- DEBUGGING_WORKFLOW.md

---

## LESSON-002: Wrong Sidebar File Edited

**Brick:** ADMIN-013A
**Date:** 2026-06-14
**Severity:** Low

**Issue:**
Sidebar Kin Alerts link was not working even after multiple fixes.

**Root Cause:**
We edited `admin/partials/sidebar.blade.php` but the layout uses `partials/admin/sidebar.blade.php`.

**Detection Method:**
Checked layout includes and found `@include('partials.admin.sidebar')`

**Prevention:**
- Always check which files are actually being included in layouts
- Use `grep -r` to find where a view is referenced

