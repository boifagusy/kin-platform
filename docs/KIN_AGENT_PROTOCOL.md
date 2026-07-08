# KIN AGENT OPERATING PROTOCOL (v1)

**Supersedes:** Change Safety & Evidence Enforcement Layer v3, QA Gate v1.0, UI/UX Quality Standard v1.0.
**Why this exists:** those three documents were good in principle but had gaps that produced real incidents — a `sed` command that inserted a duplicate import after every line in `router.jsx`, a "Safety Settings" feature marked complete with no build actually run, and a `NetworkScreen.jsx` left mid-edit with no return statement. Every rule below exists because something like it already went wrong once. Where a rule closes a specific gap, that gap is named.

This document is mandatory for every agent, tool, or session that touches the KIN codebase, including this one.

---

## 0. THE ONE RULE THAT MATTERS MORE THAN THE REST

**An agent may not claim a change works. It may only paste the output that proves it, and let the human read the proof.**

"Build passed," "should work," "tested," "production ready" are not permitted as unsupported claims anywhere in this project — not in a chat message, not in a report, not in a commit message. Every such claim must be immediately preceded by the literal terminal output it's based on. If there is no output, the correct sentence is **"NOT VERIFIED — I have not run this."**

This single rule, mechanically enforced, would have caught every incident tonight. Everything below exists to make it easy to follow and hard to skip.

---

## 1. ROLES & DECISION AUTHORITY (new — defines who owns what)

This project has a Lead Developer agent (architecture, review, governance) and one or more Implementation agents (writing files, running commands, debugging). They are typically different AI tools in separate sessions that **cannot see each other's conversations** — coordination between them can only happen through files on disk (§2) or through you relaying information manually.

**Lead Developer owns, without needing your sign-off each time** (as long as behavior is preserved): architecture, folder structure, naming conventions, refactoring, dead-code removal (only after proving it's unused), documentation, code review, and integration planning.

**Requires your explicit sign-off before implementation starts, every time:** database schema changes, authentication/session/PIN logic, API contract changes (removed/renamed endpoints, changed request/response shape), navigation or screen-flow changes, Android permissions/background services/battery handling, build/config changes (Vite, Laravel, Capacitor, env vars), and anything with meaningful risk of breaking a currently-working feature.

**Implementation agents execute** what the Lead Developer or you specify: writing files, applying patches, running builds, fixing syntax, debugging runtime errors, resolving merge conflicts. An implementation agent should not unilaterally redesign architecture or change a decision the Lead Developer already made — if it disagrees, it says so and asks, rather than overriding silently.

## 2. MULTI-AGENT COORDINATION (new — this was the actual root cause tonight)

The codebase was edited by more than one agent/session concurrently, with no signal to either party that this was happening. That is the precondition for almost everything that went wrong.

**Critical constraint: a "session lock" only works if it's written somewhere every agent can actually read.** Announcing "I'm starting an edit session" inside a chat conversation with you does nothing for an agent in a *different* chat conversation — it never sees that message. The lock must live in a file in the repo itself.

- **One active writer at a time.** Before any agent makes an edit, it must read `~/kin_project/AGENT_LOG.md` (create if absent — template in §8) and look for an open (unclosed) session entry. If one exists and looks recent, stop and tell you before touching any file — don't assume it's stale.
- **Every agent writes a session-start entry to that file before editing, and a session-end entry when done** — not before a "sprint," before *any* edit, even a one-line fix. This is a file write, not just a statement in chat.
- **Never trust a prior agent's summary of what it changed, from chat or from a report.** Verify with `git status` / `git diff` at the start of every session, always.
- **If two agents must genuinely run in parallel, they work on separate git branches** and merge deliberately, one at a time. Never two agents writing to the same working directory live.

## 3. VERSION CONTROL IS THE ONLY BACKUP MECHANISM (closes: `.backup_*` file sprawl)

The old policy allowed "git commit, git tag, backup branch, timestamped snapshot, or zip archive" as equally valid. In practice the weakest option (manual `cp file file.backup_*`) got used every time, producing dozens of stray files that cluttered `git status` and got silently deleted by later cleanup passes.

- **Git commit is the only acceptable restore point.** No `.backup_*`, `.bak`, or timestamped copies committed into the working tree. If you're tempted to `cp` a file before editing it, use git instead.
- **Commit only after a change is verified (§6), not before.** The last commit is always your known-good restore point by definition. While a change is in progress and unverified, `git diff` shows exactly what changed and `git checkout -- <file>` instantly discards it — that's the safety net for in-flight work, not a pre-emptive commit of untested code.
- **Never commit a state that failed verification.** If `npm run build` fails, fix it or revert with `git checkout -- <file>` before committing anything.
- **Any existing `.backup_*` files found in the repo get deleted after their content is confirmed already present in git history** — not left in place "just in case."

## 4. DISCOVERY BEFORE CHANGE (unchanged in spirit, tightened)

Before editing anything:

- Read the actual file you're about to change — not a summary, not a prior report's description of it.
- Run `git log --oneline -5 -- <file>` and `git diff <file>` to see real current state vs. last commit.
- Identify every other file that imports, routes to, or renders the file you're about to change.

No implementation before this. If a prior report describes a file's contents and you haven't read the file yourself in this session, treat the report as a claim, not a fact.

## 5. ROOT CAUSE BEFORE FIX (unchanged, this one was already good)

Observe → Investigate → Collect evidence → Identify root cause → Design fix → Implement → **Verify (§0)**.

Never propose a fix for a symptom without first reproducing it and identifying why it happens. A white screen is a symptom; "duplicate export" or "missing return statement" is a root cause.

## 6. SMALLEST SAFE CHANGE, ONE OBJECTIVE (unchanged in spirit)

- One objective per session. Don't fold a UI tweak, a route change, and a new backend endpoint into one pass.
- Touch the fewest files possible. List every file you intend to modify *before* starting, and treat any file outside that list as requiring a stop-and-explain.
- Never delete a component, route, service, or table without explicit approval. Deprecate, don't delete.
- Prefer append/extend over rewrite. Preserve public interfaces (component props, API shapes, exported function signatures) and backward compatibility unless the objective explicitly changes them.

## 7. THE VERIFICATION GATE (closes: features marked "done" with no real build check)

This gate is not optional and not summarizable. After every change, before saying anything is finished:

```bash
# Frontend
cd ~/kin_project/frontend
npm run build 2>&1 | tail -40
echo "EXIT CODE: $?"

# Backend (if touched)
cd ~/kin_project/backend
php artisan route:list > /dev/null 2>&1; echo "ROUTES OK: $?"
```

- **Paste the actual output, including the exit code, into the conversation.** A nonzero exit code means the change is not done, regardless of how small it looked.
- Note: `node -c` does **not** work on `.jsx` files — Node's syntax checker doesn't parse JSX. The build output above is the only valid syntax check for this project. (This is why earlier `node -c` checks in this project silently gave false confidence — they errored on file extension, not on the code.)
- If verification fails: **stop, discard the change with `git checkout -- <file>` (per §3), and re-diagnose.** Do not keep building on top of a broken state.
- **The following are never accepted as evidence, from any agent:** "It should work." "The code looks correct." "Implementation complete." "Fixed." "Done." "I believe..." "No issues expected." Each of these must be replaced by pasted command output, or by the explicit words "NOT VERIFIED."
- For UI changes specifically, verification also means: opened the page, clicked the thing, observed the actual result vs. the expected result — stated plainly, not assumed.

## 8. THE AGENT LOG (new — gives every session real history to read, and is the actual lock mechanism from §2)

Create `~/kin_project/AGENT_LOG.md` if it doesn't exist. Every agent writes a session-start entry before touching any file, and a session-end entry when finished:

```markdown
### SESSION START — 2026-07-07 20:15
**Agent:** Claude / ChatGPT / [tool name]
**Branch:** main (or feature branch name)
**Objective:** Add BottomNav to Network, Alerts, Profile screens
**Files locked:**
- src/router.jsx
- src/screens/network/NetworkScreen.jsx
- src/screens/ui-polish/AlertsScreenV2.jsx
**Expected duration:** ~20 min
**Status:** ACTIVE

### SESSION END — 2026-07-07 20:34
**Files changed:** router.jsx, NetworkScreen.jsx, AlertsScreenV2.jsx
**Verification:** npm run build → exit 0 (output pasted in chat session)
**Outstanding issues:** none
**Status:** CLOSED — committed as a1b2c3d
```

- An entry with `Status: ACTIVE` and no matching `SESSION END` is a lock. Do not edit any file it lists. Tell the human and wait.
- If a file you need is locked by a stale-looking entry (hours old, session clearly abandoned), don't assume it's safe to override — check with the human first.
- This file is the *only* place coordination actually happens between agents in separate chat sessions (see §2) — a lock stated only in chat is invisible to the other party and doesn't count.

## 9. REPORTING FORMAT (consistent across all sprints — closes: inconsistent, unverifiable reports)

Every completed change ends with this, and only this — no separate "investigation report," "failure report," or "QA report" with a different shape:

```markdown
## CHANGE REPORT
**Objective:**
**Root cause (if fixing a bug):**
**Files modified:** (exact list)
**Files created:**
**Files deleted:** (should almost always be empty — see §6)
**Verification evidence:** (paste real command + output, not a description of one)
**Rollback:** git commit hash to revert to
**Known remaining issues / explicitly out of scope:**
```

If any section would say "should work" or "probably fine" instead of pasted evidence, the report is incomplete — go back to §7.

## 10. UI/UX CONSISTENCY (condensed from prior standard — kept because it was already sound)

- Reuse existing components and design tokens before creating new ones; check `components/` before writing new markup.
- Preserve existing navigation, spacing rhythm, and interaction patterns unless the objective explicitly says to change them.
- Every async action needs a loading state; every empty list needs an explanation and a primary action; every error needs a plain-language message and a retry where applicable.
- No new icon libraries, fonts, or color values outside what's already used in the codebase, without saying so explicitly and getting confirmation.

## 11. WHEN THINGS BREAK ANYWAY

- Three failed attempts at the same fix → stop, don't try a fourth variation. Return to §4/§5 and produce a written root-cause analysis before writing more code.
- If a change breaks the build, the routes, or a previously-working screen: `git checkout -- <file>` back to the last good commit immediately (§3). Never leave the app in a worse state than before the session started, even temporarily.
- Close out the AGENT_LOG.md entry (§8) as `Status: CLOSED — reverted, no working change produced` so the next session has an honest record, rather than leaving it open or deleting it.
