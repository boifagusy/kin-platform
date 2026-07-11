#!/data/data/com.termux/files/usr/bin/bash

# State Engine — Authoritative project state
# All engines read from this, not their own state files

if [ -n "$SDK_ROOT" ]; then
    KERNEL_DIR="$SDK_ROOT/kernel"
else
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    KERNEL_DIR="$(dirname "$(dirname "$SCRIPT_DIR")")/kernel"
fi

source "$KERNEL_DIR/state.sh" 2>/dev/null
source "$KERNEL_DIR/yaml.sh" 2>/dev/null

# Single source of truth for all project state
state_get() {
    local key="$1"
    case "$key" in
        gate)             gate_current 2>/dev/null || echo "0" ;;
        gate_status)      state_read "gate.yaml" "status" 2>/dev/null | tr -d ' ' ;;
        gate_lifecycle)   state_read "gate.yaml" "lifecycle" 2>/dev/null | tr -d ' ' ;;
        role)             state_read "ai.yaml" "active_role" 2>/dev/null | tr -d ' ' ;;
        brick)            state_read "brick.yaml" "active_brick" 2>/dev/null | tr -d ' ' ;;
        brick_status)     state_read "brick.yaml" "status" 2>/dev/null | tr -d ' ' ;;
        session)          state_read "session.yaml" "status" 2>/dev/null | tr -d ' ' ;;
        approval)         state_read "gate.yaml" "approval" 2>/dev/null | tr -d ' ' ;;
        validation)       state_read "ai.yaml" "validation" 2>/dev/null | tr -d ' ' ;;
        release)          state_read "release.yaml" "version" 2>/dev/null | tr -d ' ' ;;
        git_branch)       git branch --show-current 2>/dev/null || echo "unknown" ;;
        git_status)       git status --porcelain 2>/dev/null | wc -l | tr -d ' ' ;;
        *)                echo "unknown key: $key" ;;
    esac
}

# Full state snapshot for governance decisions
state_snapshot() {
    echo "gate: $(state_get gate)"
    echo "gate_status: $(state_get gate_status)"
    echo "role: $(state_get role)"
    echo "brick: $(state_get brick)"
    echo "session: $(state_get session)"
    echo "validation: $(state_get validation)"
    echo "git_branch: $(state_get git_branch)"
    echo "git_changes: $(state_get git_status)"
}
