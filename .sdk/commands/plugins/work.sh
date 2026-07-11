# Description: Daily engineering work dashboard
# Requires: state gate workflow validate

work_main() {
    # Load engines
    source "$SDK_ROOT/engines/gate/engine.sh" 2>/dev/null
    source "$SDK_ROOT/engines/workflow/engine.sh" 2>/dev/null
    source "$SDK_ROOT/engines/validate/engine.sh" 2>/dev/null
    source "$SDK_ROOT/engines/git/engine.sh" 2>/dev/null
    
    local gate brick role project_name
    gate="$(gate_current)"
    brick="$(state_read "brick.yaml" "active_brick" 2>/dev/null | tr -d ' ')"
    role="$(state_read "ai.yaml" "active_role" 2>/dev/null | tr -d ' ')"
    project_name="$(basename "$(get_project_root 2>/dev/null)")"
    
    clear 2>/dev/null || true
    
    cat << BANNER
╔══════════════════════════════════════════════════════════════╗
║              ENGINEERING OS — DAILY DASHBOARD                ║
╠══════════════════════════════════════════════════════════════╣
║ Project: ${project_name}                                       ║
║ Gate:    $gate — $(gate_name "$gate")                          ║
║ Brick:   ${brick:-none}                                       ║
║ Role:    ${role:-unassigned}                                  ║
╠══════════════════════════════════════════════════════════════╣
BANNER
    
    # Git status
    echo "║ Git:     $(git_branch) ($(git status --porcelain 2>/dev/null | wc -l | tr -d ' ') changes)"
    
    # Session status
    local session_status
    session_status="$(state_read "session.yaml" "status" 2>/dev/null | tr -d ' ')"
    echo "║ Session: ${session_status:-inactive}"
    
    # Validation score
    local score
    score="$(validate_project 2>/dev/null | grep "Score:" | tr -d ' ' | sed 's/Score://')"
    echo "║ Health:  ${score:-unknown}"
    
    echo "╠══════════════════════════════════════════════════════════════╣"
    echo "║                                                            ║"
    
    # Blockers
    if gate_is_blocked 2>/dev/null; then
        local reason
        reason="$(state_read "gate.yaml" "blocked_reason" 2>/dev/null | tr -d ' ')"
        echo "║  ⛔ BLOCKED: $reason"
        echo "║                                                            ║"
    fi
    
    # Recommendations based on current state
    echo "║  RECOMMENDED:                                              ║"
    
    case "${session_status:-inactive}" in
        inactive) echo "║    → ai session start                                      ║" ;;
    esac
    
    case "${role:-unassigned}" in
        unassigned) echo "║    → ai role set <role>                                    ║" ;;
    esac
    
    case "${brick:-none}" in
        none) echo "║    → ai brick list (choose active brick)                   ║" ;;
        *)    echo "║    → ai workflow next                                      ║" ;;
    esac
    
    echo "║    → ai validate                                           ║"
    
    # Show pending items
    local waiting
    waiting="$(state_read "ai.yaml" "waiting_for" 2>/dev/null | tr -d ' ')"
    if [ -n "$waiting" ] && [ "$waiting" != "null" ] && [ "$waiting" != "initialization" ]; then
        echo "║                                                            ║"
        echo "║  PENDING: $waiting"
    fi
    
    echo "║                                                            ║"
    echo "╠══════════════════════════════════════════════════════════════╣"
    
    # Quick commands
    echo "║  ai work │ ai status │ ai gate │ ai brick │ ai workflow     ║"
    echo "║  ai validate │ ai doctor │ ai git │ ai release              ║"
    echo "╚══════════════════════════════════════════════════════════════╝"
    echo ""
    
    # Show next action explicitly
    echo "▶️  NEXT ACTION:"
    workflow_next 2>/dev/null | grep "NEXT:" | sed 's/▶️  //'
}

main() { work_main "$@"; }
