# Description: Manage AI roles
role_set() {
    local role="${1:-unassigned}"
    local state_dir
    state_dir="$(get_state_dir)" || return 1
    if [ ! -f "$state_dir/ai.yaml" ]; then
        echo "No active session. Run: ai session start"
        return 1
    fi
    write_yaml "$state_dir/ai.yaml" "active_role" "$role"
    write_yaml "$state_dir/session.yaml" "role" "$role"
    write_yaml "$state_dir/session.yaml" "updated" "$(date -u +%Y-%m-%dT%H:%M:%SZ)"
    echo "Role set to: $role"
}

role_status() {
    local state_dir
    state_dir="$(get_state_dir)" || return 1
    if [ -f "$state_dir/ai.yaml" ]; then
        local role
        role="$(read_yaml "$state_dir/ai.yaml" "active_role")"
        echo "Active role: ${role:-unassigned}"
    else
        echo "No active session"
    fi
}

main() {
    local action="${1:-status}"
    case "$action" in
        set)    role_set "$2" ;;
        status) role_status ;;
        *)      echo "Usage: ai role [status|set <role>]"; return 1 ;;
    esac
}
