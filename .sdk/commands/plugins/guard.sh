guard_main() {
    source "$SDK_ROOT/kernel/state.sh" 2>/dev/null
    
    local action="${1:-implement}"
    local project_gate=$(state_read "gate.yaml" "current" 2>/dev/null | tr -d ' ')
    local brick_gate=$(state_read "brick.yaml" "brick_gate" 2>/dev/null | tr -d ' ')
    local brick=$(state_read "brick.yaml" "active_brick" 2>/dev/null | tr -d ' ')
    local role=$(state_read "ai.yaml" "active_role" 2>/dev/null | tr -d ' ')
    
    # Brick gate governs implementation
    local required_gate=6
    case "$action" in
        implement|code|develop|build|fix) required_gate=6 ;;
        test|verify) required_gate=7 ;;
        release|deploy) required_gate=11 ;;
        plan|design) required_gate=5 ;;
        *) required_gate=6 ;;
    esac
    
    echo ""
    echo "═══════════════════════════════════════"
    echo "  GATE GUARD — Brick-Level"
    echo "═══════════════════════════════════════"
    echo "  Action:       $action"
    echo "  Brick:        ${brick:-none}"
    echo "  Brick Gate:   ${brick_gate:-0}"
    echo "  Project Gate: ${project_gate:-0}"
    echo "  Required:     $required_gate"
    echo "  Role:         ${role:-?}"
    echo ""
    
    local errors=0
    
    # Check brick gate (not project gate) for implementation
    if [ "${brick_gate:-0}" -lt "$required_gate" ]; then
        echo "  ❌ Brick gate ${brick_gate} too early — need $required_gate"
        echo "     → ai brick advance"
        errors=$((errors + 1))
    else
        echo "  ✅ Brick gate sufficient ($brick_gate ≥ $required_gate)"
    fi
    
    [ "${brick:-none}" = "none" ] && {
        echo "  ❌ No active brick"
        errors=$((errors + 1))
    }
    
    [ "${role:-unassigned}" = "unassigned" ] && {
        echo "  ❌ No role assigned"
        errors=$((errors + 1))
    } || echo "  ✅ Role: $role"
    
    echo ""
    if [ $errors -eq 0 ]; then
        echo "  VERDICT: ✅ Implementation ALLOWED"
    else
        echo "  VERDICT: ❌ Implementation BLOCKED — $errors issue(s)"
    fi
    echo "═══════════════════════════════════════"
}
main() { guard_main "$@"; }
