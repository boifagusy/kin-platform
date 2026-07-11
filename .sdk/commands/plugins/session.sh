# Description: Manage AI sessions
session_start() {
    local state_dir
    state_dir="$(get_state_dir)" || return 1
    local session_id
    session_id="session_$(date +%s)"
    local started
    started="$(date -u +%Y-%m-%dT%H:%M:%SZ)"

    mkdir -p "$state_dir"

    cat > "$state_dir/session.yaml" <<INNEREOF
session_id: $session_id
started: $started
updated: $started
role: unassigned
status: active
INNEREOF

    cat > "$state_dir/ai.yaml" <<INNEREOF
active_role: unassigned
current_stage: bootstrap
current_brick: none
waiting_for: initialization
last_action: none
blocked_reason: null
INNEREOF

    cat > "$state_dir/brick.yaml" <<INNEREOF
active_brick: none
brick_status: inactive
last_updated: null
documentation: null
INNEREOF

    cat > "$state_dir/github.yaml" <<INNEREOF
branch: $(git branch --show-current 2>/dev/null || echo 'unknown')
status: $(git status --porcelain 2>/dev/null | wc -l | tr -d ' ')
last_commit: $(git log -1 --format=%h 2>/dev/null || echo 'none')
pr_status: none
release_version: none
INNEREOF

    echo "Session started"
    echo "  ID:      $session_id"
    echo "  Started: $started"
}

session_stop() {
    local state_dir
    state_dir="$(get_state_dir)" || return 1
    if [ -f "$state_dir/session.yaml" ]; then
        local session_id
        session_id="$(read_yaml "$state_dir/session.yaml" "session_id")"
        cat > "$state_dir/session.yaml" <<INNEREOF
session_id: $session_id
started: $(read_yaml "$state_dir/session.yaml" "started")
updated: $(date -u +%Y-%m-%dT%H:%M:%SZ)
role: $(read_yaml "$state_dir/session.yaml" "role")
status: inactive
INNEREOF
        echo "Session stopped: $session_id"
    else
        echo "No active session"
    fi
}

session_status() {
    local state_dir
    state_dir="$(get_state_dir)" || return 1
    if [ -f "$state_dir/session.yaml" ]; then
        echo "Session:"
        echo "  ID:      $(read_yaml "$state_dir/session.yaml" "session_id")"
        echo "  Started: $(read_yaml "$state_dir/session.yaml" "started")"
        echo "  Updated: $(read_yaml "$state_dir/session.yaml" "updated")"
        echo "  Role:    $(read_yaml "$state_dir/session.yaml" "role")"
        echo "  Status:  $(read_yaml "$state_dir/session.yaml" "status")"
    else
        echo "No active session"
    fi
}

main() {
    local action="${1:-status}"
    case "$action" in
        start)  session_start ;;
        stop)   session_stop ;;
        status) session_status ;;
        *)      echo "Usage: ai session [start|stop|status]"; return 1 ;;
    esac
}
