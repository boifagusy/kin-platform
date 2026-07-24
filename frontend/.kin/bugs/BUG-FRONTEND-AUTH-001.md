# BUG-FRONTEND-AUTH-001

**Status**: Implementation Complete

**Validation**: Partial

**Gate**: Awaiting Final Verification

**Objective**

Frontend API requests were redirected to `/admin/login` instead of receiving proper API responses.

**Root Cause**

Direct `fetch()` calls in DashboardScreenV2.jsx omitted `Accept: application/json` header, causing Laravel's `expectsJson()` middleware to return 302 redirect instead of proper 401/200.

**Implementation**

Replaced 4 direct fetch calls with centralized `getDashboard()` from src/services/api.js:
- Line 110: Initial dashboard load
- Line 59: Refresh after initial load  
- Line 196: Refresh after check-in
- Line 240: Refresh after duress/emergency

Centralized API client ensures consistent headers:
```javascript
const getHeaders = () => ({
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  ...(getToken() && { 'Authorization': `Bearer ${getToken()}` })
});

Validation Status
✅ Dashboard API request returns proper response (no redirect)
✅ CORS error eliminated
✅ 401 Unauthorized on unauthenticated requests (correct API behavior)
⏳ End-to-end validation pending:
[ ] Dashboard full load with authenticated session
[ ] Trusted Contacts page access
[ ] Check-ins submission
[ ] SOS/Emergency activation
[ ] All protected endpoints return proper API response
Files Changed
src/screens/ui-polish/DashboardScreenV2.jsx
Added: import { getDashboard } from '../../services/api'
Replaced: 4 direct fetch calls with getDashboard()
Rollback
If unexpected issues occur:
Revert DashboardScreenV2.jsx to previous direct fetch implementation
No backend rollback required
No database changes
No API contract changes
No deployment required
Next Steps
Complete end-to-end validation across all protected features
Migrate remaining direct fetch calls (BottomNav.jsx, NetworkScreenV2.jsx)
Close gate when all features verified
Related
ADMIN-USER-001 (complete)
BUG-TRUSTEDCONTACT-001 (complete)
PLATFORM-HTTP-001 (pending investigation)
Created: 2026-07-24
