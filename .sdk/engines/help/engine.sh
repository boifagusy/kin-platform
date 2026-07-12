#!/data/data/com.termux/files/usr/bin/bash

help_main() {
    cat << "HELPEOF"
ENGINEERING OS v3.3 — Help System

────────────────────────────────────────
START HERE
────────────────────────────────────────
  ai work          Engineering Command Center
  ai doctor        Check environment
  ai validate      Validate system health
  ai session start Begin session
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

────────────────────────────────────────
DEVELOPMENT
────────────────────────────────────────
  ai contract      Verified contracts enforcement
  ai task          Task state machine
  ai role          Assign AI roles
  ai audit         View audit trail
  ai restore       System restoration

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
    local topic="$1"
    case "$topic" in
        discovery)
            echo "PROJECT DISCOVERY ENGINE"
            echo "  ai discovery build         Build project registry"
            echo "  ai discovery confidence    Weighted stability score"
            echo "  ai discovery diff          Compare snapshots (trend)"
            echo "  ai discovery investigate   Deep-dive a feature"
            ;;
        intelligence)
            echo "PROJECT INTELLIGENCE ENGINE"
            echo "  ai intelligence scan       Full project scan"
            echo "  ai intelligence health     Stability score + issues"
            echo "  ai intelligence roadmap    Recommended next steps"
            echo "  ai intelligence impact <f> Impact analysis"
            echo "  ai intelligence report     Complete report"
            ;;
        investigate)
            echo "INVESTIGATION ENGINE"
            echo "  ai investigate services    Scan backend services"
            echo "  ai investigate controllers Scan controllers"
            echo "  ai investigate routes      Analyze routes"
            echo "  ai investigate list        View all reports"
            ;;
        certify)
            echo "CERTIFICATION ENGINE"
            echo "  ai certify create <id> <name> <status>"
            echo "  ai certify list            All certifications"
            echo "  ai certify progress        Gate completion %"
            ;;
        gate)
            echo "GATE SYSTEM — 12 Gates (0-Bootstrap through 11-Release)"
            echo "  ai gate status | list | verify | advance | block | unblock"
            ;;
        brick)
            echo "BRICK SYSTEM"
            echo "  ai brick list | create | info | lock | unlock | validate"
            ;;
        contract)
            echo "CONTRACT ENGINE — Verified Contracts Before Implementation"
            echo "  ai contract verify | list | certify"
            ;;
        role)   echo "ROLES: architect, backend, frontend, debugger, tester, reviewer, security, docs, git, release" ;;
        workflow) echo "WORKFLOW: Investigate → Verify → Plan → Approve → Implement → Validate → Certify → Commit → Release" ;;
        release) echo "RELEASE: ai release status | suggest | checklist | changelog | create | verify" ;;
        copy)   echo "CLIPBOARD: ai copy | ai copy \"text\" | ai copy file <f> | echo \"text\" | ai" ;;
        restore) echo "RESTORE: ai restore list | report | verify | run <recipe> | run <r> --dry-run" ;;
        session) echo "SESSION: ai session start | stop | status" ;;
        git)    echo "GIT: ai git status | branch | changes | commit | tag | rollback" ;;
        commands)
            echo "ALL: work status doctor validate guard session role gate brick"
            echo "     workflow project discovery intelligence investigate certify"
            echo "     contract task event audit knowledge git github release"
            echo "     restore copy help tutorial examples explain install plugins"
            ;;
        *) echo "Topics: discovery, intelligence, investigate, certify, gate, brick, contract, role, workflow, release, copy, restore, session, git, commands" ;;
    esac
}

help_tutorial() {
    case "${1:-1}" in
        1) echo "LESSON 1: ai work → ai session start → ai role set architect → ai gate status" ;;
        2) echo "LESSON 2: ai discovery build → ai discovery confidence → ai investigate services" ;;
        3) echo "LESSON 3: ai gate verify → ai gate advance → ai work" ;;
        *) echo "Tutorial lessons: 1-3" ;;
    esac
}

help_examples() {
    case "${1:-}" in
        auth) echo "EXAMPLE: ai investigate services → ai brick create auth → ai certify create 1 \"Auth\" CERTIFIED" ;;
        release) echo "EXAMPLE: ai validate all → ai release checklist → ai release create 1.2.0" ;;
        discover) echo "EXAMPLE: ai discovery build → ai discovery confidence → ai discovery diff" ;;
        *) echo "EXAMPLES: ai examples auth | release | discover" ;;
    esac
}

help_explain() {
    local target="$1"
    local names=("Bootstrap" "Discovery" "Requirements" "Architecture" "Dependency Planning" "Brick Planning" "Brick Development" "Brick Testing" "Integration Testing" "System Testing" "Production Validation" "Release")
    
    case "$target" in
        discovery) echo "DISCOVERY ENGINE — Builds project registry with weighted scoring. Run: ai discovery confidence" ;;
        intelligence) echo "INTELLIGENCE ENGINE — Complete project understanding. Run: ai intelligence report" ;;
        investigate) echo "INVESTIGATION ENGINE — No code without research. Run: ai investigate services" ;;
        certify) echo "CERTIFICATION ENGINE — Permanent task records. Run: ai certify list" ;;
        *)
            if echo "$target" | grep -qE '^[0-9]+$' && [ "$target" -ge 0 ] && [ "$target" -le 11 ]; then
                echo "Gate $target: ${names[$target]}"
            else
                echo "Explain: ai explain <0-11> | discovery | intelligence | investigate | certify"
            fi
            ;;
    esac
}
