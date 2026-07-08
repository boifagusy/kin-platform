# KIN AGENT OPERATING PROTOCOL (v2)

## Document Philosophy

This project intentionally keeps governance documentation small.

Only these governance documents are allowed:

- AGENT_PROTOCOL.md
- ROUTING_GUIDE.md
- KIN_RUNBOOK.md
- DECISIONS.md
- PROJECT_STATUS.md
- AI_CONTEXT.md
- FEATURE_REGISTRY.md
- CODEMAP.md
- PROTECTED_FILES.md
- AGENT_LOG.md

No new governance documents should be created unless explicitly approved.

...

## Bootstrap Requirement

Every AI session MUST begin by running:

    ./scripts/ai-bootstrap.sh

The complete bootstrap output must be pasted into the AI before describing the task.

Every AI must review the bootstrap output before planning, investigation, or implementation.

Skipping the bootstrap means Discovery is incomplete and violates this protocol.

## Documentation Maintenance Rule

Whenever a task changes the project structure, workflow, architecture, or governance, the AI MUST determine which documents need updating.

Review and update when applicable:

- AGENT_PROTOCOL.md
- ROUTING_GUIDE.md
- PROJECT_STATUS.md
- KIN_RUNBOOK.md
- DECISIONS.md
- FEATURE_REGISTRY.md
- CODEMAP.md
- AI_CONTEXT.md

Documentation updates are part of the implementation, not an optional follow-up.

If no documentation changes are required, explicitly state:

"Documentation review completed — no updates required."

## Session Completion Checklist

Before declaring any task complete, every AI MUST confirm:

[ ] Bootstrap reviewed
[ ] Discovery completed
[ ] Files modified only as planned
[ ] Build/Test verification completed (or marked NOT VERIFIED)
[ ] Documentation reviewed and updated if required
[ ] DECISIONS.md updated if an architectural decision was made
[ ] AGENT_LOG.md session completed

A task is not complete until this checklist has been satisfied.

## Living Documentation

One topic = One document.

Never create files like:

FEATURE_REPORT.md
FEATURE_AUDIT.md
FEATURE_DISCOVERY.md
FEATURE_ANALYSIS.md

Update the existing document instead.

Current responsibilities:

PROJECT_STATUS.md
- Overall project progress
- Milestones
- Feature completion

AGENT_LOG.md
- Session history
- Active file locks
- Investigation history

DECISIONS.md
- Permanent architecture decisions
- ADR entries

KIN_RUNBOOK.md
- Known bugs
- Recovery procedures
- Termux commands
- Stack-specific fixes

Everything else belongs inside existing documentation.

The bootstrap script derives current repository state directly from git.

Never create:

- PROJECT_STATE.md
- SESSION_STATE.md

These are intentionally replaced by:

- git status
- git diff
- git log
- AGENT_LOG.md

which are always the source of truth.

