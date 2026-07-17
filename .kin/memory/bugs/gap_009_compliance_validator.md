# GAP-009: Compliance Validator Accuracy

## Status: PLANNED (Low Priority)
## Date: 2026-07-14

## Current Behavior
Validator uses grep. Produces false positives (brick, git, release, validate 
show compliant without API implementation). Produces false negatives 
(intelligence engine has API but shows pending).

## Expected Behavior
AST or structured parser that reliably detects Engine API compliance.

## Fix
Replace grep-based detection with structured check for api) dispatch pattern.
