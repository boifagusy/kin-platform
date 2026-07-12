# Description: Help system
help_main() {
    local action="${1:-main}"
    local help_engine="$SDK_ROOT/engines/help/engine.sh"
    
    [ -f "$help_engine" ] || { echo "Help engine not found"; return 1; }
    source "$help_engine" 2>/dev/null
    
    case "$action" in
        main)      help_main_once ;;
        tutorial)  help_tutorial "${2:-1}" ;;
        examples)  help_examples "${2:-}" ;;
        explain)   help_explain "${2:-}" ;;
        *)         help_topic "$action" ;;
    esac
}

# Prevent double call
help_main_once() {
    cat << "HELPEOF"
ENGINEERING OS — Help System

USAGE
  ai <command> [subcommand] [options]

START HERE
  ai work          Engineering Command Center
  ai doctor        Check environment
  ai validate      Validate system health
  ai session start Begin session
  ai guard         Pre-implementation check

PROJECT
  ai status        Project overview
  ai workflow      Workflow status + next action
  ai gate          Gate management
  ai brick         Brick management

DEVELOPMENT
  ai contract      Contract verification
  ai task          Task state machine
  ai role          Assign AI roles
  ai audit         View audit trail
  ai restore       System restoration

RELEASE
  ai git           Git operations
  ai github        GitHub integration
  ai release       Release management

CLIPBOARD
  ai copy "text"   Copy to Android clipboard
  ai copy all      Copy terminal history
  ai copy file <f> Copy file contents
  echo 'text' | ai Pipe to clipboard

LEARNING
  ai help <topic>  Detailed help on any topic
  ai tutorial 1    Guided walkthrough
  ai examples auth Real-world examples
  ai explain 6     Explain gate 6 or a brick

Topics: guard, gate, brick, role, workflow, release, copy, restore, session, git, commands
HELPEOF
}

main() { help_main "$@"; }
