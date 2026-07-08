# KIN ROUTING GUIDE (v2)

This document defines the KIN AI development workflow.

It complements AGENT_PROTOCOL.md.

AGENT_PROTOCOL.md defines the rules.

ROUTING_GUIDE.md defines how every AI should work.

---

# 1. Bootstrap (Mandatory)

Every AI session MUST begin by running:

    ./scripts/ai-bootstrap.sh

Paste the complete bootstrap output into the AI before describing the task.

The bootstrap is the shared memory between AI sessions.

Never skip it.

---

# 2. Execution Modes

Every AI must declare one execution mode.

## Repository-Aware

Can inspect the repository.

Can search files.

Can run discovery independently.

## Bootstrap-Only

Has bootstrap output only.

Cannot inspect repository.

Must request only implementation-specific files.

Must not request governance documents.

## Repository-Blind

No repository access.

No bootstrap.

Must request bootstrap first.

---

# 3. Lead Developer

Responsibilities

• Architecture
• Planning
• Risk analysis
• File selection
• Reviews
• Approval

Lead Dev does NOT implement code by default.

Lead Dev produces:

• Architecture
• Implementation plan
• File list
• Risks
• Approval

---

# 4. Implementation Agent

Implementation begins only after approval.

Default workflow:

1. Inspect repository
2. Modify repository
3. Verify
4. Update documentation
5. Update AGENT_LOG.md
6. Return implementation summary

Repository is the implementation.

Chat is the report.

---

# 5. Termux Workflow

KIN is developed directly inside Termux.

Preferred editing methods:

1. Python replacement scripts
2. Python file generation
3. apply_patch
4. sed (small edits only)

Avoid:

• Large cat <<EOF replacements
• Massive chat code dumps
• Full file rewrites unless necessary

Every command must be copy-paste compatible with Termux.

---

# 6. Documentation Workflow

Every implementation reviews:

• PROJECT_STATUS.md
• FEATURE_REGISTRY.md
• CODEMAP.md
• AI_CONTEXT.md
• DECISIONS.md
• KIN_RUNBOOK.md
• AGENT_PROTOCOL.md
• ROUTING_GUIDE.md

Update when required.

Otherwise state:

Documentation review completed — no updates required.

---

# 7. Repository First Principle

Repository = implementation

Chat =

• Planning
• Approval
• Verification
• Summary

Do not paste complete source files unless requested.

---

# 8. Typical Workflow

Bootstrap

↓

Lead Dev

↓

Approval

↓

Implementation

↓

Verification

↓

Documentation

↓

AGENT_LOG

↓

Implementation Summary

---

# 9. Implementation Summary

Return only:

• Files created
• Files modified
• Verification results
• Documentation updated
• Remaining blockers
• Next recommendation

---

# 10. Session Completion

Before finishing:

[ ] Bootstrap reviewed

[ ] Discovery complete

[ ] Verification complete

[ ] Documentation reviewed

[ ] AGENT_LOG updated

[ ] Summary returned

A task is not complete until every item above is satisfied.
