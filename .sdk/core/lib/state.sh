#!/data/data/com.termux/files/usr/bin/bash

# Initialize project state
init_state() {
    local state_dir
    state_dir="$(get_state_dir)" || return 1
    mkdir -p "$state_dir"

    cat > "$state_dir/ai.yaml" <<'INNEREOF'
active_role: unassigned
current_stage: bootstrap
current_brick: none
waiting_for: initialization
last_action: none
blocked_reason: null
INNEREOF

    cat > "$state_dir/brick.yaml" <<'INNEREOF'
active_brick: none
brick_status: inactive
last_updated: null
documentation: null
INNEREOF

    cat > "$state_dir/github.yaml" <<'INNEREOF'
branch: unknown
status: clean
last_commit: none
pr_status: none
release_version: none
INNEREOF
}

# Load session state
load_session() {
    local state_dir
    state_dir="$(get_state_dir)" || return 1
    if [ -f "$state_dir/session.yaml" ]; then
        cat "$state_dir/session.yaml"
    else
        echo "session: inactive"
    fi
}

# Save session state
save_session() {
    local state_dir session_id started role
    state_dir="$(get_state_dir)" || return 1
    session_id="${1:-unknown}"
    started="${2:-unknown}"
    role="${3:-unassigned}"
    mkdir -p "$state_dir"
    cat > "$state_dir/session.yaml" <<INNEREOF
session_id: $session_id
started: $started
updated: $(date -u +%Y-%m-%dT%H:%M:%SZ)
role: $role
status: active
INNEREOF
}

# Load AI state
load_ai_state() {
    local state_dir
    state_dir="$(get_state_dir)" || return 1
    cat "$state_dir/ai.yaml" 2>/dev/null || echo "ai: inactive"
}

# Update a single AI state field
save_ai_state() {
    local state_dir key value
    state_dir="$(get_state_dir)" || return 1
    key="$1"
    value="$2"
    write_yaml "$state_dir/ai.yaml" "$key" "$value"
}
