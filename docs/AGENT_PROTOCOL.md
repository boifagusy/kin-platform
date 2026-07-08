# KIN AGENT OPERATING PROTOCOL (v2)

## Version History

### v1.0 (2026-07-08)
- Initial governance system established.
- Mandatory bootstrap process introduced.
- Documentation Maintenance Rule added.
- Session Completion Checklist added.
- Environment Capability Check added.
- Living Documentation rule established.
- Multi-agent coordination standardized.

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

## Documentation Update Policy

Implementation completion does NOT automatically update feature status.

Workflow:

1. Implementation Agent writes code.
2. Build verification is performed.
3. AGENT_LOG.md is updated.
4. Documentation is reviewed.
5. Feature documents (PROJECT_STATUS.md, FEATURE_REGISTRY.md, CODEMAP.md, AI_CONTEXT.md) are updated ONLY after the human confirms the feature works correctly.

Until human verification:

- Mark runtime verification as PENDING.
- Do NOT mark features as Complete.
- Do NOT change project milestones.
- Do NOT change feature status from In Progress to Complete.

The Implementation Agent may update governance documents (AGENT_PROTOCOL.md, ROUTING_GUIDE.md, KIN_RUNBOOK.md, DECISIONS.md) immediately when required because they describe the development process, not application functionality.

The human is the final authority for functional verification.

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


## Environment Capability Check

At the beginning of Discovery, every AI MUST declare its execution mode.

### Mode 1 — Repository-Aware
- Direct filesystem access.
- Can inspect, search and verify the repository.
- Performs full Discovery independently.

### Mode 2 — Bootstrap-Only
- Has the bootstrap output only.
- Can understand project state but cannot inspect repository files.
- Must request only implementation-specific files that are genuinely required.
- Must NOT request governance documents already covered by the bootstrap.

### Mode 3 — Repository-Blind
- No repository access and no bootstrap output.
- Must request the bootstrap output first.
- After reviewing it, request only the minimum implementation-specific files needed.

Discovery is not complete until the execution mode has been declared.


## Repository First Principle

The repository is the single source of truth.

The chat is used only for:

- Planning
- Architecture decisions
- Approvals
- Verification reports
- Implementation summaries

Unless explicitly requested, implementation belongs in the repository—not in the chat.

## Termux Implementation Workflow

KIN is developed directly inside a local Termux environment.

After implementation approval, every Implementation AI MUST:

1. Inspect the repository.
2. Modify repository files directly.
3. Verify changes.
4. Update required documentation.
5. Update AGENT_LOG.md.
6. Return a concise implementation summary.

Preferred modification methods:

1. Python replacement scripts
2. Python file generation
3. apply_patch (when available)
4. sed for small edits only

Avoid:

- Large cat <<EOF blocks
- Rewriting entire files unnecessarily
- Returning complete source files unless explicitly requested

Every command must be copy-paste compatible with Termux.

The repository is the implementation.

The chat is the implementation report.

## Implementation Authorization

Implementation begins only after explicit approval from the Lead Developer or the project owner.

Approval authorizes the AI to:

- Modify the repository directly.
- Create new files.
- Update existing files.
- Run verification commands.
- Update required documentation.
- Complete AGENT_LOG.md.

Approval does NOT authorize the AI to paste complete source files into the chat.

After approval, implementation belongs in the repository.

## Code Delivery Rules

Implementation approval means:

- Modify the repository directly.
- Run verification.
- Update required documentation.
- Complete AGENT_LOG.md.
- Return only a summary of the work.

Implementation summaries should include:

- Files created
- Files modified
- Verification results
- Documentation updated
- Remaining blockers

Do not paste complete source files unless explicitly requested.

## Governance Maintenance

AI Governance is part of the project architecture.

Governance documents should remain stable.

Do not add new rules unless a real problem has been observed.

When governance changes are made:

- Update PROJECT_STATUS.md
- Update AI_CONTEXT.md if onboarding changes
- Update ROUTING_GUIDE.md if workflow changes
- Update AGENT_PROTOCOL.md if rules change
- Update ai-bootstrap.sh if bootstrap changes
- Record significant governance decisions in DECISIONS.md

Avoid governance growth through duplicate or overlapping sections.

Improve existing documents instead of creating new governance documents whenever possible.

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

