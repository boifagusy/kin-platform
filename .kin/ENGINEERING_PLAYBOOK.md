# KIN Engineering Playbook

**Version:** 1.0.0
**Status:** Draft
**Authority:** Single Source of Truth

---

# Purpose

This document defines the engineering standards, workflow, governance, and AI operating instructions for the KIN Platform.

All engineering work must follow this playbook unless explicitly superseded by an approved Brick specification.

---

# Table of Contents

1. Engineering Constitution
2. Engineering Workflow
3. Gate System
4. Brick Lifecycle
5. Investigation Standards
6. Validation Standards
7. Impact Analysis
8. Implementation Standards
9. Verification Standards
10. Certification
11. Documentation Standards
12. Session Resume Rules
13. AI Operating Instructions
14. Project Directory Standards
15. Templates
16. Appendices

---

> This document is the authoritative engineering guide for the project.
# 1. Engineering Constitution

## Mission

Build software that is:

- Correct before fast
- Simple before clever
- Maintainable before optimized
- Evidence-driven before assumption-driven

---

## Engineering Principles

Every engineering activity shall follow these principles:

1. Evidence First
   - Verify before concluding.
   - Do not assume runtime behavior.

2. Single Responsibility
   - Each Brick has one objective.
   - Each Finding documents one investigation.

3. Traceability
   - Every decision must be traceable to evidence.
   - Every implementation must reference an approved Brick.

4. Separation of Concerns
   - Investigation
   - Validation
   - Impact Analysis
   - Implementation
   - Verification
   - Certification

   Each phase is independent.

5. Controlled Change
   - No implementation without authorization.
   - No architectural changes without review.

---

## Non-Negotiable Rules

The following rules are mandatory:

- Never skip engineering gates.
- Never mix investigation with implementation.
- Never treat assumptions as evidence.
- Never modify production behavior during investigation.
- Always document findings before implementation.
- Maintain backward compatibility unless an approved Brick specifies otherwise.

---

## Engineering Philosophy

Investigate first.

Understand second.

Implement third.

Verify fourth.

Certify last.


# 2. Engineering Workflow

## Standard Lifecycle

Every Brick follows the same lifecycle.

```
Discovery
    ↓
Investigation
    ↓
Analysis
    ↓
Validation
    ↓
Approval
    ↓
Implementation
    ↓
Verification
    ↓
Certification
```

---

## Phase Definitions

### Discovery

Understand the problem and define the scope.

Deliverables:

- Brick created
- Scope defined
- Dependencies identified

---

### Investigation

Collect evidence only.

Allowed:

- Read code
- Inspect runtime
- Document findings

Not Allowed:

- Code changes
- Refactoring
- Optimization

Deliverable:

- Findings report

---

### Analysis

Review investigation results.

Deliverables:

- Risks
- Dependencies
- Unknowns
- Impact summary

---

### Validation

Validate assumptions independently.

Examples:

- Sandbox testing
- API validation
- Runtime verification
- Platform verification

Deliverable:

- Validation report

---

### Approval

Engineering review.

Possible outcomes:

- Approved
- Approved with conditions
- Rejected
- Deferred

---

### Implementation

Only begins after approval.

Deliverables:

- Source code
- Tests
- Documentation updates

---

### Verification

Confirm implementation matches requirements.

Activities:

- Testing
- Regression checks
- Runtime verification

---

### Certification

Final engineering acceptance.

Deliverables:

- Completion report
- Brick closed
- Documentation updated

---

## Golden Rule

Never skip a phase.

A later phase cannot replace an earlier phase.


| Field | Value |
|-------|-------|
| Version | 1.1.0 |
| Status | Active |
| Owner | KIN Engineering |
| Authority | Single Source of Truth |
| Last Updated | 2026-07-24 |
| Next Review | After Gate System completion |

## Change Log

### v1.1.0
- Added Engineering Constitution
- Added Engineering Workflow

