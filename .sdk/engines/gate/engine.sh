#!/data/data/com.termux/files/usr/bin/bash

# Determine paths
if [ -n "$SDK_ROOT" ]; then
    ENGINES_DIR="$SDK_ROOT/engines"
    KERNEL_DIR="$SDK_ROOT/kernel"
else
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    ENGINES_DIR="$(dirname "$SCRIPT_DIR")"
    KERNEL_DIR="$(dirname "$ENGINES_DIR")/kernel"
fi

# Source kernel
if [ -f "$KERNEL_DIR/common.sh" ]; then
    source "$KERNEL_DIR/common.sh"
    source "$KERNEL_DIR/errors.sh"
    source "$KERNEL_DIR/logger.sh"
    source "$KERNEL_DIR/state.sh"
    source "$KERNEL_DIR/yaml.sh"
else
    echo "ERROR: Kernel not found at $KERNEL_DIR" >&2
    return 1
fi

# Source definitions
DEF_FILE="$ENGINES_DIR/gate/definitions.sh"
if [ -f "$DEF_FILE" ]; then
    source "$DEF_FILE"
else
    echo "ERROR: Definitions not found at $DEF_FILE" >&2
    return 1
fi

gate_current() {
    local g
    g="$(state_read "gate.yaml" "current" 2>/dev/null | tr -d ' ')"
    echo "${g:-0}"
}

gate_status() {
    local g
    g="$(state_read "gate.yaml" "status" 2>/dev/null | tr -d ' ')"
    echo "${g:-pending}"
}

gate_is_blocked() {
    local blocked
    blocked="$(state_read "gate.yaml" "blocked" 2>/dev/null | tr -d ' ')"
    [ "$blocked" = "true" ]
}

gate_info() {
    local current name desc
    current="$(gate_current)"
    name="$(gate_name "$current")"
    desc="$(gate_description "$current")"

    echo "GATE $current: $name"
    echo "═══════════════════════════════════════"
    echo "  Status:    $(gate_status)"
    echo "  Blocked:   $(gate_is_blocked && echo 'true' || echo 'false')"
    echo "  Purpose:   $desc"
}

gate_verify() {
    local current
    current="$(gate_current)"
    echo "Verifying Gate $current: $(gate_name "$current")"
    
    if [ "$current" -gt 0 ]; then
        local prev=$((current - 1))
        local prev_status
        prev_status="$(state_read "gate.yaml" "gate_${prev}_status" 2>/dev/null | tr -d ' ')"
        if [ "$prev_status" != "completed" ]; then
            echo "  Gate $prev not completed"
            return 1
        fi
    fi
    
    echo "  Gate $current verification passed"
    return 0
}

gate_advance() {
    local current next total
    current="$(gate_current)"
    next=$((current + 1))
    total="$(gate_count)"
    
    if [ "$next" -ge "$total" ]; then
        echo "All gates completed"
        return 0
    fi
    
    state_write "gate.yaml" "gate_${current}_status" "completed"
    state_write "gate.yaml" "current" "$next"
    state_write "gate.yaml" "status" "active"
    state_write "gate.yaml" "blocked" "false"
    state_write "gate.yaml" "blocked_reason" "null"
    
    echo "Advanced to Gate $next: $(gate_name "$next")"
}

gate_goto() {
    local target="$1" current
    current="$(gate_current)"
    
    if [ "$target" -le "$current" ]; then
        echo "Cannot go backwards (from $current to $target)"
        return 1
    fi
    
    for ((g=current; g<target; g++)); do
        state_write "gate.yaml" "gate_${g}_status" "completed"
    done
    
    state_write "gate.yaml" "current" "$target"
    state_write "gate.yaml" "status" "active"
    state_write "gate.yaml" "blocked" "false"
    echo "Now at Gate $target: $(gate_name "$target")"
}

gate_block() {
    local reason="${1:-no reason given}"
    state_write "gate.yaml" "blocked" "true"
    state_write "gate.yaml" "blocked_reason" "$reason"
    echo "Gate blocked: $reason"
}

gate_unblock() {
    state_write "gate.yaml" "blocked" "false"
    state_write "gate.yaml" "blocked_reason" "null"
    echo "Gate unblocked"
}

gate_list() {
    local current
    current="$(gate_current)"
    
    echo "GATES (0-11)"
    echo "═══════════════════════════════════════"
    
    for def in "${GATE_DEFINITIONS[@]}"; do
        local num="${def%%:*}"
        local rest="${def#*:}"
        local name="${rest%%:*}"
        
        if [ "$num" = "$current" ]; then
            echo "  ▶ Gate $num: $name (CURRENT)"
        elif [ "$num" -lt "$current" ]; then
            echo "  ✅ Gate $num: $name"
        else
            echo "  ⏳ Gate $num: $name"
        fi
    done
}

gate_init() {
    state_init 2>/dev/null || true
    
    local total
    total="$(gate_count)"
    
    state_write "gate.yaml" "current" "0"
    state_write "gate.yaml" "status" "active"
    state_write "gate.yaml" "blocked" "false"
    state_write "gate.yaml" "blocked_reason" "null"
    
    for ((i=0; i<total; i++)); do
        if [ "$i" -eq 0 ]; then
            state_write "gate.yaml" "gate_${i}_status" "active"
        else
            state_write "gate.yaml" "gate_${i}_status" "pending"
        fi
    done
    
    echo "Gate system initialized. Current: Gate 0 - Bootstrap"
}

# ── Standard Engine API ──
gate_help()    { echo "Gate Engine — 12-gate lifecycle. ai gate [status|list|verify|advance|block|unblock]"; }
gate_version() { echo "Gate Engine v3.2.1"; }
gate_health()  { echo "✅ Gate Engine healthy — Gate $(gate_current 2>/dev/null)"; return 0; }
gate_doctor()  { echo "Gate: $(gate_current 2>/dev/null) | Status: $(state_read gate.yaml status 2>/dev/null) | Blocked: $(state_read gate.yaml blocked 2>/dev/null)"; }
gate_validate(){ gate_verify 2>/dev/null && echo "✅ Gate valid" || echo "❌ Gate invalid"; }

# API dispatch
if [ "${1:-}" = "api" ]; then
    case "${2:-help}" in
        help) gate_help ;; version) gate_version ;; status) gate_status 2>/dev/null ;;
        health) gate_health ;; doctor) gate_doctor ;; validate) gate_validate ;;
    esac
    exit 0
fi
