
## TECH-DEBT-004: Password Reset OTP Premature Consumption

**Brick:** AUTH-006
**Severity:** Medium
**Discovered:** 2026-06-15
**Status:** DEFERRED
**Priority:** LOW

**Root Cause:**
`resetPin()` calls `verifyOtp()` which marks OTP as used BEFORE the PIN update completes. If PIN update fails, user must request a new OTP.

**Current Behavior:**
- OTP consumed on verification, not on successful PIN update

**Expected Behavior:**
- OTP consumed only after PIN successfully updated

**Fix:** Move `used = true` from `verifyOtp()` to after successful PIN update in `resetPin()`

**Resolution Date:** Not yet resolved

