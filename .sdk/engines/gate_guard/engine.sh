#!/data/data/com.termux/files/usr/bin/bash

# Gate Guard — Pre-implementation enforcement
# No code generation permitted without passing this check

if [ -n "$SDK_ROOT" ]; then
    KERNEL_DIR="$SDK_ROOT/kernel"
    ENGINES_DIR="$SDK_ROOT/engines"
else
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    ENGINES_DIR="$(dirname "$SCRIPT_DIR")"
    KERNEL_DIR="$(dirname "$ENGINES_DIR")/kernel"
fi

source "$KERNEL_DIR/state.sh" 2>/dev/null
source "$KERNEL_DIR/yaml.sh" 2>/dev/null
source "$ENGINES_DIR/gate/engine.sh" 2>/dev/null

# The mandatory check — called before ANY implementation
gate_guard_check() {
    local requested_action="${1:-implementation}"
    local errors=0
    local blocks=""
    
    echo ""
    echo "═══════════════════════════════════════"
    echo "  ENGINEERING OS — GATE GUARD"
    echo "═══════════════════════════════════════"
    echo ""

    # 1. Session active?
    local session_status
    session_status="$(state_read "session.yaml" "status" 2>/dev/null | tr -d ' ')"
    if [ "$session_status" != "active" ]; then
        echo "  ❌ CHECK 1: No active session"
        blocks="$blocks\n  • Start session: ai session start"
        errors=$((errors + 1))
    else
        echo "  ✅ Session: active"
    fi

    # 2. Role assigned?
    local role
    role="$(state_read "ai.yaml" "active_role" 2>/dev/null | tr -d ' ')"
    if [ "${role:-unassigned}" = "unassigned" ]; then
        echo "  ❌ CHECK 2: No role assigned"
        blocks="$blocks\n  • Assign role: ai role set <role>"
        errors=$((errors + 1))
    else
        echo "  ✅ Role: $role"
    fi

    # 3. Current gate
    local current_gate
    current_gate="$(gate_current 2>/dev/null)"
    echo "  ✅ Current Gate: $current_gate — $(gate_name "$current_gate" 2>/dev/null)"

    # 4. What gate does this action require?
    local required_gate
    case "$requested_action" in
        implement|code|develop|build|fix|modify)
            required_gate=6 ;;  # Brick Development
        test|verify_test)
            required_gate=7 ;;  # Brick Testing
        integrate)
            required_gate=8 ;;  # Integration Testing
        release|deploy)
            required_gate=11 ;; # Release
        plan|design)
            required_gate=5 ;;  # Brick Planning
        architect|architecture)
            required_gate=3 ;;  # Architecture
        *)
            required_gate="$current_gate" ;;
    esac

    # 5. Gate check — is current gate sufficient?
    if [ "$current_gate" -lt "$required_gate" ]; then
        echo "  ❌ CHECK 4: Gate too early for $requested_action"
        echo "     Required Gate: $required_gate — $(gate_name "$required_gate" 2>/dev/null)"
        echo "     Current Gate:  $current_gate — $(gate_name "$current_gate" 2>/dev/null)"
        local steps=$((required_gate - current_gate))
        blocks="$blocks\n  • Advance $steps gate(s): ai gate verify && ai gate advance"
        errors=$((errors + 1))
    else
        echo "  ✅ Gate: $requested_action allowed at Gate $current_gate"
    fi

    # 6. Active brick?
    local brick
    brick="$(state_read "brick.yaml" "active_brick" 2>/dev/null | tr -d ' ')"
    if [ "$required_gate" -ge 6 ] && [ "${brick:-none}" = "none" ]; then
        echo "  ❌ CHECK 5: No active brick for implementation"
        blocks="$blocks\n  • Create or select brick: ai brick create <name>"
        errors=$((errors + 1))
    else
        echo "  ✅ Brick: ${brick:-none}"
    fi

    # 7. Brick locked?
    if [ "${brick:-none}" != "none" ] && [ -f "bricks/$brick/brick.yaml" ]; then
        local locked locked_by
        locked="$(yaml_get_nested "bricks/$brick/brick.yaml" "brick" "locked" 2>/dev/null)"
        locked_by="$(yaml_get_nested "bricks/$brick/brick.yaml" "brick" "locked_by" 2>/dev/null)"
        if [ "$locked" = "true" ] && [ "$locked_by" != "${role:-unknown}" ]; then
            echo "  ❌ CHECK 6: Brick locked by $locked_by"
            blocks="$blocks\n  • Request unlock or wait for $locked_by"
            errors=$((errors + 1))
        elif [ "$locked" = "true" ]; then
            echo "  ✅ Brick locked by you ($role)"
        else
            echo "  ⚠️  Brick unlocked — consider locking: ai brick lock $brick $role"
        fi
    fi

    # 8. Approval required?
    if [ "$current_gate" -eq 3 ] || [ "$current_gate" -eq 11 ]; then
        echo "  ⚠️  CHECK 7: Gate $current_gate requires Engineering Manager approval"
        blocks="$blocks\n  • Await Engineering Manager approval"
        errors=$((errors + 1))
    else
        echo "  ✅ Approval: not required for Gate $current_gate"
    fi

    # 9. Blocked?
    local blocked blocked_reason
    blocked="$(state_read "gate.yaml" "blocked" 2>/dev/null | tr -d ' ')"
    if [ "$blocked" = "true" ]; then
        blocked_reason="$(state_read "gate.yaml" "blocked_reason" 2>/dev/null)"
        echo "  ❌ CHECK 8: Gate is blocked — $blocked_reason"
        blocks="$blocks\n  • Resolve: $blocked_reason"
        errors=$((errors + 1))
    else
        echo "  ✅ Not blocked"
    fi

    # 10. Validation state
    local health
    health=$(bash "$ENGINES_DIR/validate/engine.sh" 2>/dev/null | grep "Score:" | head -1 | tr -d ' ' | sed 's/Score://')
    echo "  ✅ Health: ${health:-?}%"

    # VERDICT
    echo ""
    echo "═══════════════════════════════════════"
    if [ $errors -eq 0 ]; then
        echo "  VERDICT: ✅ PASS — Proceed with $requested_action"
        echo "═══════════════════════════════════════"
        echo ""
        return 0
    else
        echo "  VERDICT: ❌ BLOCKED — $errors issue(s)"
        echo "═══════════════════════════════════════"
        echo ""
        echo "  REQUIRED ACTIONS:"
        echo -e "$blocks"
        echo ""
        echo "  No code generation permitted until all checks pass."
        echo ""
        return 1
    fi
}

# Gate guard can also check a specific action
gate_guard_can() {
    local action="$1"
    gate_guard_check "$action"
    return $?
}

# Run check if called directly
if [ "${1:-}" = "check" ]; then
    gate_guard_check "${2:-implementation}"
fi
