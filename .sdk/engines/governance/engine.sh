#!/data/data/com.termux/files/usr/bin/bash

# GOVERNANCE ENGINE v1.1 — Mandatory CLI Gateway
# No command executes without passing governance.
# Cannot be bypassed by any AI agent.

if [ -n "$SDK_ROOT" ]; then
    KERNEL_DIR="$SDK_ROOT/kernel"
    ENGINES_DIR="$SDK_ROOT/engines"
    META_DIR="$SDK_ROOT/engines/governance/metadata"
else
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    ENGINES_DIR="$(dirname "$SCRIPT_DIR")"
    KERNEL_DIR="$(dirname "$ENGINES_DIR")/kernel"
    META_DIR="$SCRIPT_DIR/metadata"
fi

source "$KERNEL_DIR/state.sh" 2>/dev/null
source "$KERNEL_DIR/yaml.sh" 2>/dev/null
source "$ENGINES_DIR/gate/engine.sh" 2>/dev/null

# Read engine metadata if available
governance_metadata() {
    local engine="$1"
    local meta_file="$META_DIR/${engine}.yaml"
    if [ -f "$meta_file" ]; then
        cat "$meta_file"
    else
        echo "requires: { gate: 0, role: [], brick: optional, approval: false, validation: none }"
    fi
}

governance_check() {
    local command="${1:-unknown}"
    local target="${2:-}"
    local errors=0
    local first_failure=""
    
    # Read metadata for this command
    local meta
    meta="$(governance_metadata "$command")"
    
    local project session role gate brick
    project="$(get_project_root 2>/dev/null | xargs basename 2>/dev/null)"
    session="$(state_read "session.yaml" "status" 2>/dev/null | tr -d ' ')"
    role="$(state_read "ai.yaml" "active_role" 2>/dev/null | tr -d ' ')"
    gate="$(gate_current 2>/dev/null)"
    brick="$(state_read "brick.yaml" "active_brick" 2>/dev/null | tr -d ' ')"
    
    echo ""
    echo "═══════════════════════════════════════"
    echo "  GOVERNANCE ENGINE v1.1"
    echo "═══════════════════════════════════════"
    echo "  ${project:-?} | ${role:-?} | Gate $gate — $(gate_name "$gate" 2>/dev/null) | ${brick:-no brick}"
    echo "═══════════════════════════════════════"
    echo "  Command: $command ${target:+→ $target}"
    echo ""
    
    # ── Guard 1: Context ──
    [ -z "$project" ] && { echo "  ❌ Not in a project directory"; errors=$((errors + 1)); first_failure="context"; }
    
    # ── Guard 2: Session ──
    if [ "$session" != "active" ]; then
        echo "  ❌ No active session → ai session start"
        errors=$((errors + 1)); first_failure="${first_failure:-session}"
    else
        echo "  ✅ Session active"
    fi
    
    # ── Guard 3: Role ──
    if [ "${role:-unassigned}" = "unassigned" ]; then
        echo "  ❌ No role assigned → ai role set <role>"
        errors=$((errors + 1)); first_failure="${first_failure:-role}"
    else
        echo "  ✅ Role: $role"
    fi
    
    # ── Guard 4: Gate ──
    local required_gate=0
    case "$command" in
        implement|code|develop|build|fix|modify|patch|generate|scaffold|migrate|restore_run) required_gate=6 ;;
        test|verify_test) required_gate=7 ;;
        integrate|merge) required_gate=8 ;;
        release|deploy|publish) required_gate=11 ;;
        plan|design|architect) required_gate=5 ;;
        brick) required_gate=5 ;;
        *) required_gate="$gate" ;;
    esac
    
    if [ "$gate" -lt "$required_gate" ]; then
        echo "  ❌ Gate $gate too early → Need Gate $required_gate — $(gate_name "$required_gate" 2>/dev/null)"
        echo "     → ai gate verify && ai gate advance"
        errors=$((errors + 1)); first_failure="${first_failure:-gate}"
    else
        echo "  ✅ Gate $gate — $(gate_name "$gate" 2>/dev/null)"
    fi
    
    # ── Guard 5: Brick ──
    if [ "$required_gate" -ge 6 ]; then
        if [ "${brick:-none}" = "none" ]; then
            echo "  ❌ No active brick → ai brick create <name>"
            errors=$((errors + 1)); first_failure="${first_failure:-brick}"
        elif [ ! -d "bricks/$brick" ]; then
            echo "  ❌ Brick '$brick' not found"
            errors=$((errors + 1)); first_failure="${first_failure:-brick}"
        else
            echo "  ✅ Brick: $brick"
        fi
    fi
    
    # ── Guard 6: Approval ──
    if [ "$gate" -eq 3 ] || [ "$gate" -eq 11 ]; then
        echo "  ❌ Gate $gate requires Engineering Manager approval"
        errors=$((errors + 1)); first_failure="${first_failure:-approval}"
    fi
    
    # ── Guard 7: Blocked ──
    local blocked
    blocked="$(state_read "gate.yaml" "blocked" 2>/dev/null | tr -d ' ')"
    if [ "$blocked" = "true" ]; then
        local reason
        reason="$(state_read "gate.yaml" "blocked_reason" 2>/dev/null)"
        echo "  ❌ Gate blocked: $reason"
        errors=$((errors + 1)); first_failure="${first_failure:-blocked}"
    fi
    
    # ── VERDICT ──
    echo ""
    echo "═══════════════════════════════════════"
    
    if [ $errors -eq 0 ]; then
        echo "  STATUS: ✅ PASS"
        echo "  Proceed with: $command ${target:+$target}"
        echo "═══════════════════════════════════════"
        echo ""
        return 0
    else
        echo "  STATUS: ❌ BLOCKED — $errors issue(s)"
        echo "  First failure: $first_failure guard"
        echo "═══════════════════════════════════════"
        echo ""
        echo "  ⛔ No execution performed."
        echo "  ⛔ Fix issues above and retry."
        echo ""
        return 1
    fi
}
