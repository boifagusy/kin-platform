#!/data/data/com.termux/files/usr/bin/bash

help_main() {
    cat << "HELPEOF"
ENGINEERING OS — Help System

USAGE
  ai <command> [subcommand] [options]

ENFORCEMENT
  ai guard         Gate Guard — pre-implementation check

START HERE
  ai work          Engineering Command Center
  ai doctor        Check environment
  ai validate      Validate system health
  ai session start Begin session

PROJECT
  ai status        Project overview
  ai workflow      Workflow status + next action
  ai gate          Gate management
  ai brick         Brick management

DEVELOPMENT
  ai role          Assign AI roles
  ai audit         View audit trail
  ai restore       System restoration

RELEASE
  ai git           Git operations
  ai github        GitHub integration
  ai release       Release management

CLIPBOARD
  ai clip copy     Copy to Android clipboard
  ai clip history  View clipboard history
  echo 'text' | ai Pipe to clipboard

LEARNING
  ai help <topic>  Detailed help (gate, brick, role, workflow, release, clip, restore)
  ai tutorial 1    Guided walkthrough
  ai examples auth Real-world examples
  ai explain 6     Explain gate 6 or a brick

Topics: gate, brick, role, workflow, release, clip, restore, session, git, commands
HELPEOF
}

help_topic() {
    local topic="$1"
    case "$topic" in
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
            echo "  Lifecycle: planned -> in_development -> testing -> complete -> released"
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
            echo "WORKFLOW"
            echo "  Bootstrap -> Discovery -> Requirements -> Architecture ->"
            echo "  Dependency Planning -> Brick Planning -> Development ->"
            echo "  Testing -> Integration -> System Testing -> Production -> Release"
            echo "  Commands: ai workflow status | ai workflow next"
            ;;
        release)
            echo "RELEASE: ai release status | suggest | checklist | changelog | create | verify"
            ;;
        clip)
            echo "CLIPBOARD ENGINE"
            echo "  ai clip copy 'text'   Copy to Android clipboard"
            echo "  ai clip history        View saved copies"
            echo "  ai clip last           Retrieve last copy"
            echo "  ai clip status         Quick status"
            echo "  echo 'text' | ai       Pipe to clipboard"
            echo ""
            echo "  Requires: Termux:API APK from F-Droid"
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
        commands)
            echo "ALL COMMANDS: work status doctor validate session role gate brick workflow event audit knowledge git github release restore clip help tutorial examples explain install plugins"
            ;;
        *) echo "Topics: gate, brick, role, workflow, release, clip, restore, session, git, commands" ;;
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
        2) echo "LESSON 2: Creating Your First Brick"
           echo "  Step 1: ai brick create authentication"
           echo "  Step 2: ai brick info authentication"
           echo "  Step 3: ai brick lock authentication AI-1"
           echo "  Step 4: Develop in bricks/authentication/"
           echo "  Step 5: ai validate brick authentication"
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
              echo "  ai brick create authentication"
              echo "  ai brick lock authentication AI-1"
              echo "  ai role set backend_developer"
              echo "  ai validate brick authentication"
              echo "  ai brick unlock authentication"
              echo "  ai gate verify && ai gate advance" ;;
        release) echo "EXAMPLE: Create a Release"
                 echo "  ai validate all"
                 echo "  ai release checklist"
                 echo "  ai release changelog"
                 echo "  ai release create 1.2.0"
                 echo "  ai git tag v1.2.0" ;;
        clip) echo "EXAMPLE: Clipboard Usage"
              echo "  ai clip copy 'Bug: Login 404 — Fix: route:clear'"
              echo "  ai gate status | ai  # Copy gate status"
              echo "  ai clip history       # View all copies"
              echo "  ai clip last          # Retrieve & copy last" ;;
        restore) echo "EXAMPLE: System Restore"
              echo "  ai restore verify          # Check all components"
              echo "  ai restore run watchtower  # Full state restore"
              echo "  ai restore run w --dry-run # Preview first" ;;
        *) echo "EXAMPLES: ai examples auth | release | clip | restore" ;;
    esac
}

help_explain() {
    local target="$1"
    local names=("Bootstrap" "Discovery" "Requirements" "Architecture" "Dependency Planning" "Brick Planning" "Brick Development" "Brick Testing" "Integration Testing" "System Testing" "Production Validation" "Release")
    
    if [ -n "$target" ] && [ -d "bricks/$target" ] 2>/dev/null; then
        echo "BRICK: $target"
        grep -q "status:" "bricks/$target/brick.yaml" 2>/dev/null && echo "Status: $(grep "status:" "bricks/$target/brick.yaml" | sed 's/.*: //')"
        echo "Path: bricks/$target/"
    elif echo "$target" | grep -qE '^[0-9]+$' && [ "$target" -ge 0 ] && [ "$target" -le 11 ]; then
        echo "Gate $target: ${names[$target]}"
        [ "$target" -lt 11 ] && echo "Next: Gate $((target + 1)) — ${names[$((target + 1))]}"
    else
        echo "Explain: ai explain <0-11> | ai explain <brick-name>"
        echo "Examples: ai explain 6 | ai explain authentication"
    fi
}
