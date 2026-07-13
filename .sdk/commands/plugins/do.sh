do_main() {
    local task="$*"
    [ -z "$task" ] && { echo "Usage: ai do <task>"; return 1; }
    source "$SDK_ROOT/kernel/state.sh" 2>/dev/null
    
    local role; role="$(state_read "ai.yaml" "active_role" 2>/dev/null | tr -d ' ')"
    local brick="general"
    echo "$task" | grep -qi "safety" && brick="safety_score"
    echo "$task" | grep -qi "watchtower" && brick="watchtower"
    echo "$task" | grep -qi "sos" && brick="sos"
    echo "$task" | grep -qi "auth" && brick="authentication"
    
    state_write "brick.yaml" "active_brick" "$brick" 2>/dev/null
    mkdir -p "bricks/$brick"
    
    echo "▶ $task"
    echo "  Role: ${role:-architect} | Brick: $brick"
    echo "  ai done when ready"
}
main() { do_main "$@"; }
