
## TECH-DEBT-001: Emergency Window Hardcoded

**Location:** `app/Services/EmergencyPermissionService.php`
**Current:** 24 hours hardcoded
**Future:** Should be configurable via admin settings
**Risk:** Low for MVP
**Created:** 2026-06-13


## TECH-DEBT-002: Safety Trend Chart Uses Static Data

**Location:** `app/Services/Admin/SafetyMonitorService.php`
**Current:** Hardcoded weekly trend data
**Future:** Generate from historical metrics table
**Priority:** Medium
**Impact:** Dashboard analytics accuracy
**Created:** 2026-06-13


## TECH-DEBT-003: Emergency Dashboard Cached for 60 Seconds

**Location:** `app/Services/Admin/SafetyMonitorService.php`
**Current:** Cache TTL = 60 seconds
**Future:** Configurable cache duration or real-time for emergencies
**Priority:** Low
**Impact:** Emergency alerts may be delayed up to 60 seconds
**Created:** 2026-06-13


## TECH-DEBT-004: Password Reset OTP Premature Consumption

**Brick:** AUTH-006
**Severity:** Medium
**Discovered:** 2026-06-15

**Root Cause:**
`resetPin()` calls `verifyOtp()` which marks OTP as used BEFORE the PIN update completes. If PIN update fails, user must request a new OTP.

**Current Behavior:**
- OTP consumed on verification, not on successful PIN update

**Expected Behavior:**
- OTP consumed only after PIN successfully updated

**Fix:** Move `used = true` from `verifyOtp()` to after successful PIN update in `resetPin()`

**Status:** DEFERRED (post-MVP)
**Priority:** Low (user-friendly improvement, not security)

