# BUG-RETENTION-001

**Status**: Investigation Required

**Objective**

Frontend displays hardcoded "Can remove in 1 days" instead of reading configured retention period from backend.

**Symptoms**

- Admin sets retention to 0 ("Never Delete")
- Setting saved correctly to database
- Frontend still shows "Can remove in 1 days"
- Indicates frontend is not reading from API response

**Expected Behavior**

Frontend should display:
- Retention = 0 → "Account will never be automatically removed"
- Retention = 7 → "Can remove in 7 days"
- Retention = 30 → "Can remove in 30 days"
- etc.

**Investigation Tasks**

1. Verify API Response
   - Find the endpoint that returns account/contact deletion info
   - Check if response includes deleted_account_retention_days
   - If missing: add it

2. Verify Frontend State
   - Find where "Can remove in 1 days" is rendered
   - Trace where the value comes from
   - Determine if it's hardcoded or from API

3. Trace Data Flow
   - Confirm frontend is reading from API response
   - Confirm React state is updated correctly
   - Confirm UI component uses state value

**Root Cause Candidates**

- Backend API doesn't include deleted_account_retention_days in response
- Frontend hardcodes "1" instead of reading from API
- Frontend reads API but uses default/fallback value
- UI component ignores state and uses constant

**Do Not Start**

- UI refactoring
- Component rewrites

**Start With**

- Evidence gathering: trace the complete data flow
- Identify exactly where the value is lost
- Minimal fix to restore flow

**Related**

- ADMIN-USER-001 (backend setting storage — complete)

**Created**: 2026-07-23
