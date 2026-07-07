# KIN OS — Protection Protocol

## Golden Rule
Never modify working production code unless the user explicitly requests that module to be changed.

Default behavior: Preserve → Understand → Plan → Implement → Verify

## Protected Areas
- Authentication, PIN Logic, OTP Logic, API Contracts
- Database Schema, Laravel Actions, Business Rules
- Navigation, Shared Components, Routes, Middleware, Models

## Never Delete
- Production files, Controllers, Models, Actions, Services
- Screens, Components, Routes, Documentation

## Implementation Workflow
1. Discovery → 2. Impact Analysis → 3. Implementation Plan → WAIT FOR APPROVAL
4. Implementation → 5. Verification → WAIT FOR USER TESTING
6. Backup → 7. Git Commit → 8. Update Documentation

## Success Criteria
- Requested feature works
- Existing functionality still works
- User manually verifies behavior
- Backup created after verification
- Git commit created after verification
- Registry updated
- No unrelated files modified
