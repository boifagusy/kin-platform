# ARCH-011 — ONBOARDING PERSISTENCE

**Status:** DRAFT
**Date:** 2026-06-23
**Brick:** AUTH-007
**Phase:** ARCHITECTURE

---

## 1. PROBLEM

Onboarding draft data is stored only in frontend localStorage. If user clears data or switches devices, progress is lost.

---

## 2. EVIDENCE

### Current Implementation
- `onboardingDraftService.js` — localStorage only
- `onboardingStorage.js` — abstraction layer
- `User.php` — `onboarding_completed` boolean only
- No backend draft storage

### Affected Screens
- PhoneEntryScreenV2
- CreatePinScreenV2
- UserDetailsScreenV2
- TrustedContactScreenV2
- DuressPinSetupScreenV2

---

## 3. OPTIONS

### Option A — Extend Users Table
Add `onboarding_draft` JSON column to `users` table.

**Pros:** Simple, no new table
**Cons:** Mixed concerns, large JSON

### Option B — New Onboarding Drafts Table
Create `onboarding_drafts` table with `user_id`, `draft`, `step`, `updated_at`.

**Pros:** Clean separation, auditable history
**Cons:** New table migration

### Option C — Hybrid
Use users table for draft, separate table for history/audit.

**Pros:** Best of both
**Cons:** Most complex

---

## 4. DECISION

**Recommendation:** Option B — New Onboarding Drafts Table

**Rationale:**
- Clean separation of concerns
- Auditable history
- Easy to extend later
- No JSON bloat in users table

---

## 5. RISKS

| Risk | Impact | Mitigation |
|------|--------|------------|
| Migration failure | Low | Test on dev first |
| API failure | Low | localStorage fallback |
| Sync conflict | Medium | Versioned drafts |

---

## 6. IMPLEMENTATION APPROACH

### Phase 1: Backend
- Create `onboarding_drafts` table
- Add `GET /api/v1/onboarding/draft`
- Add `POST /api/v1/onboarding/draft`

### Phase 2: Frontend
- Extend `onboardingDraftService` to sync with API
- Add localStorage fallback

### Phase 3: Migration
- Migrate existing drafts to backend

---

## STATUS

| Step | Status |
|------|--------|
| Problem | ✅ Defined |
| Evidence | ✅ Collected |
| Options | ✅ Evaluated |
| Decision | ✅ Made |
| Risks | ✅ Identified |
| Implementation | ✅ Planned |

---

**End of ARCH-011**
