#!/data/data/com.termux/files/usr/bin/bash

help_main() {
    cat << "HELPEOF"
ENGINEERING OS v3.3 — Help System

USAGE
  ai <command> [subcommand] [options]

────────────────────────────────────────
START HERE
────────────────────────────────────────
  ai work          Engineering Command Center
  ai doctor        Check environment
  ai validate      Validate system health
  ai session start Begin session
  ai guard         Pre-implementation check

────────────────────────────────────────
PROJECT
────────────────────────────────────────
  ai status        Project overview
  ai workflow      Workflow status + next action
  ai gate          Gate management (12 gates)
  ai brick         Brick management
  ai project       Project orchestrator

────────────────────────────────────────
INVESTIGATE & CERTIFY (v3.3)
────────────────────────────────────────
  ai investigate   Scan services, find gaps, assess risks
  ai certify       Create task certification records
  ai contract      Verify contracts before implementation
  ai task          Task state machine

────────────────────────────────────────
DEVELOPMENT
────────────────────────────────────────
  ai role          Assign AI roles
  ai audit         View audit trail
  ai restore       System restoration
  ai knowledge     Search knowledge base

────────────────────────────────────────
RELEASE
────────────────────────────────────────
  ai git           Git operations
  ai github        GitHub integration
  ai release       Release management

────────────────────────────────────────
CLIPBOARD
────────────────────────────────────────
  ai copy          Save screen + copy to Android clipboard
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

Topics: gate, brick, role, workflow, release, investigate,
        certify, contract, copy, restore, session, git, commands
HELPEOF
}

help_topic() {
    local topic="$1"
    case "$topic" in
        investigate)
            echo "INVESTIGATION ENGINE"
            echo ""
            echo "  No implementation begins before investigation."
            echo ""
            echo "COMMANDS:"
            echo "  ai investigate services    Scan backend services"
            echo "  ai investigate controllers Scan controllers"
            echo "  ai investigate routes      Analyze routes"
            echo "  ai investigate models      Check models"
            echo "  ai investigate list        View all reports"
            echo ""
            echo "OUTPUT:"
            echo "  • Findings — what exists"
            echo "  • Gaps — what's missing"
            echo "  • Risks — what could fail"
            echo "  • Recommendation — READY or NEEDS_ATTENTION"
            echo ""
            echo "If gaps found → approval required before implementation"
            ;;
        certify)
            echo "CERTIFICATION ENGINE"
            echo ""
            echo "  Every task generates a permanent certification record."
            echo ""
            echo "COMMANDS:"
            echo "  ai certify create <id> <name> <status>"
            echo "  ai certify list              All certifications"
            echo "  ai certify progress          Gate completion %"
            echo ""
            echo "CONSUMED BY:"
            echo "  Gate Engine, Brick Engine, Audit Engine,"
            echo "  Restore Engine, Release Engine, Dashboard"
            ;;
        gate)
            echo "GATE SYSTEM — 12 Gates"
            echo "  0  Bootstrap           1  Discovery"
            echo "  2  Requirements        3  Architecture"
            echo "  4  Dependency Planning 5  Brick Planning"
            echo "  6  Brick Development   7  Brick Testing"
            echo "  8  Integration Testing 9  System Testing"
            echo "  10 Production Validation 11 Release"
            echo ""
            echo "Commands: ai gate status | list | verify | advance | block | unblock"
            ;;
        brick)
            echo "BRICK SYSTEM"
            echo "  Lifecycle: planned → in_development → testing → complete → released"
            echo "  Structure: contracts/ events/ database/ backend/ frontend/ api/ tests/ docs/"
            echo "  Commands: ai brick list | create | info | lock | unlock | validate"
            ;;
        role)
            echo "AI ROLES"
            echo "  Engineering Manager, Architect, Planner, Backend Developer,"
            echo "  Frontend Developer, Debugger, Tester, Reviewer, Security Engineer,"
            echo "  Documentation Engineer, Git Manager, Release Manager"
            echo "  Commands: ai role status | ai role set <role>"
            ;;
        workflow)
            echo "MANDATORY WORKFLOW"
            echo "  Investigate → Verify → Plan → Approve →"
            echo "  Implement → Validate → Certify → Commit → Release"
            echo ""
            echo "  Commands: ai workflow status | ai workflow next"
            ;;
        release)
            echo "RELEASE: ai release status | suggest | checklist | changelog | create | verify"
            ;;
        copy|clip)
            echo "CLIPBOARD ENGINE"
            echo "  ai copy               Save screen + copy to Android clipboard"
            echo "  ai copy \"text\"        Copy text directly"
            echo "  ai copy file <f>      Copy file contents"
            echo "  ai copy history       View saved copies"
            echo "  echo \"text\" | ai      Pipe to clipboard"
            ;;
        restore)
            echo "RESTORE ENGINE"
            echo "  ai restore list              Available recipes"
            echo "  ai restore report            Restoration history"
            echo "  ai restore verify            Verify all components"
            echo "  ai restore run <recipe>      Execute restore"
            echo "  ai restore run <r> --dry-run Preview actions"
            ;;
        session)
            echo "SESSION: ai session start | stop | status"
            ;;
        git)
            echo "GIT: ai git status | branch | changes | commit | tag | rollback"
            ;;
        contract)
            echo "CONTRACT ENGINE"
            echo "  ai contract verify   Verify implementation against contracts"
            echo "  ai contract list     All registered contracts"
            echo "  ai contract certify  Certify task for Gate 6"
            echo "  Principle: Verified Contracts Before Implementation"
            ;;
        commands)
            echo "ALL COMMANDS:"
            echo "  work status doctor validate guard session role"
            echo "  gate brick workflow project investigate certify"
            echo "  contract task event audit knowledge"
            echo "  git github release restore copy"
            echo "  help tutorial examples explain install plugins"
            ;;
        *) 
            echo "Topics: investigate, certify, gate, brick, role, workflow,"
            echo "        release, contract, copy, restore, session, git, commands"
            echo "Run 'ai help' for the full menu."
            ;;
    esac
}

help_tutorial() {
    local lesson="${1:-1}"
    case "$lesson" in
        1) echo "LESSON 1: Starting Your First Session"
           echo "  Step 1: ai work"
           echo "  Step 2: ai session start"
           echo "  Step 3: ai role set architect"
           echo "  Step 4: ai gate status"
           echo "  Step 5: ai work"
           echo "  Next: ai tutorial 2" ;;
        2) echo "LESSON 2: Investigate Before You Build"
           echo "  Step 1: ai investigate services"
           echo "  Step 2: Review gaps and risks"
           echo "  Step 3: ai contract verify"
           echo "  Step 4: ai certify create 1 \"Discovery\" CERTIFIED"
           echo "  Next: ai tutorial 3" ;;
        3) echo "LESSON 3: Advancing Through Gates"
           echo "  Step 1: ai gate status"
           echo "  Step 2: ai gate verify"
           echo "  Step 3: ai gate advance"
           echo "  Step 4: ai work" ;;
        *) echo "Tutorial lessons: 1-3" ;;
    esac
}

help_examples() {
    local ex="${1:-}"
    case "$ex" in
        auth) echo "EXAMPLE: Build Authentication Brick"
              echo "  ai investigate services"
              echo "  ai brick create authentication"
              echo "  ai brick lock authentication AI-1"
              echo "  ai role set backend_developer"
              echo "  ai validate brick authentication"
              echo "  ai certify create 1 \"Auth\" CERTIFIED" ;;
        release) echo "EXAMPLE: Create a Release"
                 echo "  ai investigate services"
                 echo "  ai validate all"
                 echo "  ai release checklist"
                 echo "  ai release create 1.2.0"
                 echo "  ai certify create 1 \"Release\" CERTIFIED" ;;
        investigate) echo "EXAMPLE: Investigation Flow"
                     echo "  ai investigate services"
                     echo "  ai investigate controllers"
                     echo "  ai investigate routes"
                     echo "  ai investigate list"
                     echo "  # Review reports, approve, then implement" ;;
        *) echo "EXAMPLES: ai examples auth | release | investigate" ;;
    esac
}

help_explain() {
    local target="$1"
    local names=("Bootstrap" "Discovery" "Requirements" "Architecture" "Dependency Planning" "Brick Planning" "Brick Development" "Brick Testing" "Integration Testing" "System Testing" "Production Validation" "Release")
    
    if [ "$target" = "investigate" ]; then
        echo "INVESTIGATION ENGINE — No code without research"
        echo "Scans existing code, finds gaps, assesses risks."
        echo "Blocks implementation if gaps are found."
        echo "Run: ai investigate services"
    elif [ "$target" = "certify" ]; then
        echo "CERTIFICATION ENGINE — Permanent task records"
        echo "Every completed task generates a certification artifact."
        echo "Consumed by Gate, Brick, Audit, Restore, and Release engines."
        echo "Run: ai certify list"
    elif [ -n "$target" ] && [ -d "bricks/$target" ] 2>/dev/null; then
        echo "BRICK: $target"
    elif echo "$target" | grep -qE '^[0-9]+$' && [ "$target" -ge 0 ] && [ "$target" -le 11 ]; then
        echo "Gate $target: ${names[$target]}"
        [ "$target" -lt 11 ] && echo "Next: Gate $((target + 1)) — ${names[$((target + 1))]}"
    else
        echo "Explain: ai explain <0-11> | ai explain <brick> | ai explain investigate | certify"
    fi
}
