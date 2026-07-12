#!/data/data/com.termux/files/usr/bin/bash

engine_help_main() {
    cat << "HELPEOF"
ENGINEERING OS v3.3 — Help System

────────────────────────────────────────
START HERE
────────────────────────────────────────
  ai work          Engineering Command Center
  ai doctor        Check environment
  ai validate      Validate system health
  ai session start Begin session
  ai role set <r>  Assign AI role
  ai guard         Pre-implementation check

────────────────────────────────────────
PROJECT INTELLIGENCE
────────────────────────────────────────
  ai discovery      Build registry + confidence scores
  ai intelligence   Full project intelligence report
  ai investigate    Pre-implementation research
  ai certify        Task certification records

────────────────────────────────────────
PROJECT
────────────────────────────────────────
  ai status        Project overview
  ai workflow      Workflow status + next action
  ai gate          Gate management (12 gates)
  ai brick         Brick management
  ai project       Project orchestrator
  ai contract      Contract verification
  ai task          Task state machine

────────────────────────────────────────
DEVELOPMENT
────────────────────────────────────────
  ai audit         View audit trail
  ai restore       System restoration
  ai knowledge     Search knowledge base
  ai event         Event system

────────────────────────────────────────
RELEASE
────────────────────────────────────────
  ai git           Git operations
  ai github        GitHub integration
  ai release       Release management

────────────────────────────────────────
CLIPBOARD
────────────────────────────────────────
  ai copy          Save screen + copy to clipboard
  ai copy "text"   Copy text directly
  ai copy file <f> Copy file contents
  echo "text" | ai Pipe to clipboard

────────────────────────────────────────
UTILITIES
────────────────────────────────────────
  ai governance    Governance check
  ai install       Install into project
  ai plugins       List plugins

────────────────────────────────────────
LEARNING
────────────────────────────────────────
  ai help <topic>  Detailed help on any topic
  ai tutorial 1    Guided walkthrough
  ai examples auth Real-world examples
  ai explain 6     Explain gate 6 or a brick

────────────────────────────────────────
MANDATORY WORKFLOW
────────────────────────────────────────
  Investigate → Verify → Plan → Approve →
  Implement → Validate → Certify → Commit → Release

Topics: discovery, intelligence, investigate, certify,
        gate, brick, role, workflow, release, contract,
        copy, restore, session, git, commands
HELPEOF
}

help_topic() {
    case "$1" in
        gate)    echo "GATE SYSTEM — 12 Gates (0-11). ai gate status | list | verify | advance" ;;
        brick)   echo "BRICK SYSTEM. ai brick list | create | info | lock | unlock | validate" ;;
        role)    echo "ROLES: architect, backend, frontend, debugger, tester, reviewer. ai role set <role>" ;;
        workflow) echo "WORKFLOW: Investigate → Verify → Plan → Approve → Implement → Validate → Certify → Release" ;;
        release) echo "RELEASE: ai release status | suggest | checklist | changelog | create | verify" ;;
        contract) echo "CONTRACT: ai contract verify | list | certify" ;;
        copy)    echo "CLIPBOARD: ai copy | ai copy \"text\" | ai copy file <f> | echo \"text\" | ai" ;;
        restore) echo "RESTORE: ai restore list | report | verify | run <recipe>" ;;
        session) echo "SESSION: ai session start | stop | status | reset" ;;
        git)     echo "GIT: ai git status | branch | changes | commit | tag | rollback | log" ;;
        discover*) echo "DISCOVERY: ai discovery build | confidence | diff | investigate" ;;
        intelligence) echo "INTELLIGENCE: ai intelligence scan | health | roadmap | impact | report" ;;
        investigate) echo "INVESTIGATE: ai investigate services | controllers | routes | list" ;;
        certify) echo "CERTIFY: ai certify create <id> <name> <status> | list | progress" ;;
        commands) echo "ALL: work status doctor validate guard session role gate brick workflow project discover intelligence investigate certify contract task event audit knowledge git github release restore copy help tutorial examples explain governance install plugins" ;;
        *) echo "Topics: gate, brick, role, workflow, release, contract, copy, restore, session, git, commands" ;;
    esac
}

help_tutorial() {
    case "${1:-1}" in
        1) echo "LESSON 1: ai work → ai session start → ai role set architect → ai gate status" ;;
        2) echo "LESSON 2: ai discovery build → ai discovery confidence → ai investigate services" ;;
        3) echo "LESSON 3: ai gate advance → ai brick create <name> → ai certify create 1 Task CERTIFIED" ;;
        *) echo "Tutorials: 1-3" ;;
    esac
}

help_examples() {
    case "${1:-}" in
        auth) echo "EXAMPLE: ai investigate services → ai brick create auth → ai certify create 1 Auth CERTIFIED" ;;
        release) echo "EXAMPLE: ai validate all → ai release checklist → ai release create 1.2.0" ;;
        *) echo "EXAMPLES: ai examples auth | release" ;;
    esac
}

help_explain() {
    case "${1:-}" in
        discovery) echo "Discovery Engine — Builds project registry. ai discovery confidence" ;;
        intelligence) echo "Intelligence Engine — Complete project understanding. ai intelligence report" ;;
        investigate) echo "Investigation Engine — No code without research. ai investigate services" ;;
        certify) echo "Certification Engine — Permanent task records. ai certify list" ;;
        *) echo "Explain: ai explain <0-11> | discovery | intelligence | investigate | certify" ;;
    esac
}
