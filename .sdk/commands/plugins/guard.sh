guard_main() {
    source "$SDK_ROOT/engines/gate/engine.sh" 2>/dev/null
    source "$SDK_ROOT/kernel/state.sh" 2>/dev/null
    
    local gate=$(state_read "gate.yaml" "current" 2>/dev/null | tr -d ' ')
    gate="${gate:-0}"
    local role=$(state_read "ai.yaml" "active_role" 2>/dev/null | tr -d ' ')
    local brick=$(state_read "brick.yaml" "active_brick" 2>/dev/null | tr -d ' ')
    local action="${1:-implement}"
    
    local required_gate=0
    case "$action" in
        implement|code|develop|build|fix) required_gate=6 ;;
        test|verify) required_gate=7 ;;
        integrate) required_gate=8 ;;
        release|deploy) required_gate=11 ;;
        plan|design) required_gate=5 ;;
    esac
    
    echo ""
    echo "═══════════════════════════════════════"
    echo "  GATE GUARD"
    echo "═══════════════════════════════════════"
    echo "  Request: $action | Gate: $gate | Required: $required_gate"
    echo "  Role: ${role:-?} | Brick: ${brick:-?}"
    echo ""
    
    local errors=0
    
    [ "$gate" -lt "$required_gate" ] && {
        echo "  ❌ Gate $gate too early — Need Gate $required_gate"
        echo "     → ai gate advance (x$((required_gate - gate)))"
        errors=$((errors + 1))
    } || echo "  ✅ Gate OK"
    
    [ "$required_gate" -ge 6 ] && [ "${brick:-none}" = "none" ] && {
        echo "  ❌ No active brick"
        errors=$((errors + 1))
    }
    
    [ "${role:-unassigned}" = "unassigned" ] && {
        echo "  ❌ No role assigned"
        errors=$((errors + 1))
    } || echo "  ✅ Role: $role"
    
    echo ""
    if [ $errors -eq 0 ]; then
        echo "  VERDICT: ✅ PASS — Proceed with $action"
    else
        echo "  VERDICT: ❌ BLOCKED — $errors issue(s)"
    fi
    echo "═══════════════════════════════════════"
}
main() { guard_main "$@"; }
