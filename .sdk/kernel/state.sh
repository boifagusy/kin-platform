#!/data/data/com.termux/files/usr/bin/bash

# Source dependencies
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/filesystem.sh" 2>/dev/null || true
source "$SCRIPT_DIR/yaml.sh" 2>/dev/null || true

# Get project root
get_project_root() {
    git rev-parse --show-toplevel 2>/dev/null
}

# Get state directory
get_state_dir() {
    local root
    root="$(get_project_root)" || {
        echo ""
        return 1
    }
    echo "$root/.kin/state"
}

# Initialize all state files
state_init() {
    local state_dir
    state_dir="$(get_state_dir)" || return 1
    ensure_dir "$state_dir"

    # session.yaml
    local session_id="session_$(date +%s)"
    local now
    now="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
    atomic_write "$state_dir/session.yaml" \
"session_id: $session_id
started: $now
updated: $now
role: unassigned
status: active"

    # ai.yaml
    atomic_write "$state_dir/ai.yaml" \
"active_role: unassigned
current_gate: 0
current_brick: none
waiting_for: initialization
last_action: none
blocked_reason: null"

    # gate.yaml
    atomic_write "$state_dir/gate.yaml" \
"current: 0
status: active
started: $now
blocked: false"

    # brick.yaml
    atomic_write "$state_dir/brick.yaml" \
"active_brick: none
status: inactive
gate: 0
last_updated: null"

    # github.yaml
    local branch status commit
    branch="$(git branch --show-current 2>/dev/null || echo 'unknown')"
    status="$(git status --porcelain 2>/dev/null | wc -l | tr -d ' ')"
    commit="$(git log -1 --format=%h 2>/dev/null || echo 'none')"
    atomic_write "$state_dir/github.yaml" \
"branch: $branch
status: $status
last_commit: $commit
pr_number: null
pr_status: none"

    log_info "State initialized: $state_dir"
    return 0
}

# Read a state value
state_read() {
    local state_file="$1"
    local key="$2"
    local state_dir
    state_dir="$(get_state_dir)" || return 1
    yaml_get "$state_dir/$state_file" "$key"
}

# Write a state value
state_write() {
    local state_file="$1"
    local key="$2"
    local value="$3"
    local state_dir
    state_dir="$(get_state_dir)" || return 1
    yaml_set "$state_dir/$state_file" "$key" "$value"
    # Update session timestamp
    local now
    now="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
    yaml_set "$state_dir/session.yaml" "updated" "$now"
}

# Check if state is initialized
state_exists() {
    local state_dir
    state_dir="$(get_state_dir)" 2>/dev/null || return 1
    [ -f "$state_dir/session.yaml" ] && [ -f "$state_dir/ai.yaml" ]
}

# Validate state integrity
state_validate() {
    local state_dir
    state_dir="$(get_state_dir)" || return 1
    local errors=0
    
    for file in session.yaml ai.yaml gate.yaml brick.yaml github.yaml; do
        if [ ! -f "$state_dir/$file" ]; then
            log_warn "Missing state file: $file"
            errors=$((errors + 1))
        elif ! yaml_validate "$state_dir/$file"; then
            log_error "Corrupt state file: $file"
            errors=$((errors + 1))
        fi
    done
    
    return $errors
}

# Repair state by reinitializing corrupt files
state_repair() {
    log_info "Repairing state..."
    state_validate >/dev/null 2>&1
    local result=$?
    if [ $result -ne 0 ]; then
        log_warn "$result state files need repair - reinitializing"
        state_init
    fi
}
