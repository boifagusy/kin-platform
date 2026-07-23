# BUG-TRUSTEDCONTACT-001

**Status**: Investigation Required

**Objective**

Fix Trusted Contact removal cooldown display and calculation.

**Symptoms**

- Displays raw floating-point: "You can remove in 0.6613699435532396 days"
- Uses hardcoded REMOVAL_COOLDOWN_DAYS = 30
- Poor user experience

**Expected Behavior**

Display human-friendly text:
- "You can remove this contact in 1 day."
- "You can remove this contact in 16 hours."
- "You can remove this contact tomorrow."

Never display floating-point decimals.

**Scope**

Separate from user account deletion retention (ADMIN-USER-001).

This is purely about the trusted contact relationship lifecycle.

**Investigation Tasks**

1. Verify REMOVAL_COOLDOWN_DAYS purpose (prevent abuse? configurable?)
2. Fix calculation: created_at + cooldown_days
3. Format output: human-friendly, not decimal
4. Test: Various time periods before/after cooldown expires

**Root Cause Candidates**

- Hardcoded constant should be admin-configurable
- Decimal output formatting (diffInDays returns float)
- Poor error message

**Related**

- ADMIN-USER-001 (user account lifecycle — separate)

**Created**: 2026-07-23
