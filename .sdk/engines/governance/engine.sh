#!/data/data/com.termux/files/usr/bin/bash

# GOVERNANCE ENGINE v2.1 — Policy-driven with Evidence

if [ -n "$SDK_ROOT" ]; then
    KERNEL_DIR="$SDK_ROOT/kernel"
    ENGINES_DIR="$SDK_ROOT/engines"
    GOV_DIR="$SDK_ROOT/engines/governance"
else
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    ENGINES_DIR="$(dirname "$SCRIPT_DIR")"
    KERNEL_DIR="$(dirname "$ENGINES_DIR")/kernel"
    GOV_DIR="$SCRIPT_DIR"
fi

source "$KERNEL_DIR/state.sh" 2>/dev/null
source "$ENGINES_DIR/gate/engine.sh" 2>/dev/null
source "$GOV_DIR/levels.sh" 2>/dev/null
source "$GOV_DIR/codes.sh" 2>/dev/null
source "$ENGINES_DIR/policy/engine.sh" 2>/dev/null
source "$ENGINES_DIR/state_engine/engine.sh" 2>/dev/null

governance_check() {
    local command="${1:-unknown}"
    local target="${2:-}"
    local level; level="$(governance_level "$command")"
    local level_name; level_name="$(governance_level_name "$level")"
    
    # Level 0 — Skip entirely
    [ "$level" -eq 0 ] && return 0
    
    # Get authoritative state
    local gate role brick session
    gate="$(state_get gate)"
    role="$(state_get role)"
    brick="$(state_get brick)"
    session="$(state_get session)"
    
    echo ""
    echo "═══════════════════════════════════════"
    echo "  GOVERNANCE ENGINE v2.1"
    echo "  Level $level — $level_name"
    echo "  Policy-driven with Evidence"
    echo "═══════════════════════════════════════"
    echo "  $(state_snapshot | head -1)"
    echo "  Command: $command ${target:+→ $target}"
    echo ""
    
    local errors=0
    local fail_guard=""
    local now; now="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
    
    # Level 1+: Session + Role
    [ "$session" != "active" ] && { fail_guard="${fail_guard:-session}"; errors=$((errors + 1)); } || echo "  ✅ Session active"
    [ "${role:-unassigned}" = "unassigned" ] && { fail_guard="${fail_guard:-role}"; errors=$((errors + 1)); } || echo "  ✅ Role: $role"
    
    # Level 2+: Gate
    if [ "$level" -ge 2 ]; then
        local required_gate=0
        case "$command" in
            implement|code|develop|build|fix|modify|patch|generate|scaffold|migrate|restore_run) required_gate=6 ;;
            test|verify_test) required_gate=7 ;;
            integrate|merge) required_gate=8 ;;
            release|deploy|publish) required_gate=11 ;;
            plan|design|architect|brick) required_gate=5 ;;
            *) required_gate="$gate" ;;
        esac
        
        if [ "$gate" -lt "$required_gate" ]; then
            fail_guard="${fail_guard:-gate}"; errors=$((errors + 1))
        else
            echo "  ✅ Gate $gate — $(gate_name "$gate" 2>/dev/null)"
        fi
    fi
    
    # Level 3+: Brick + Approval
    if [ "$level" -ge 3 ]; then
        [ "${brick:-none}" = "none" ] && { fail_guard="${fail_guard:-brick}"; errors=$((errors + 1)); } || echo "  ✅ Brick: $brick"
        [ "$gate" -eq 3 ] || [ "$gate" -eq 11 ] && { fail_guard="${fail_guard:-approval}"; errors=$((errors + 1)); }
    fi
    
    # Level 4: Git clean
    if [ "$level" -ge 4 ]; then
        git diff --quiet 2>/dev/null || { fail_guard="${fail_guard:-git_clean}"; errors=$((errors + 1)); }
    fi
    
    # Save evidence
    mkdir -p .kin/evidence
    cat > ".kin/evidence/governance_${command}_${now}.yaml" << YAML
governance_check:
  command: $command
  timestamp: $now
  level: $level
  state:
    gate: $gate
    role: $role
    brick: $brick
    session: $session
  verdict: $([ $errors -eq 0 ] && echo "PASS" || echo "BLOCKED")
  failed_guard: ${fail_guard:-none}
YAML
    
    echo ""
    echo "═══════════════════════════════════════"
    
    if [ $errors -eq 0 ]; then
        echo "  DECISION: ✅ PASS"
        echo "  EVIDENCE: .kin/evidence/governance_${command}_${now}.yaml"
        echo "═══════════════════════════════════════"
        echo ""
        return 0
    else
        local code; code="$(decision_code "$fail_guard")"
        echo "  DECISION: ❌ BLOCKED"
        echo "  CODE:     $code"
        echo "  REASON:   $(decision_reason "$fail_guard")"
        echo "  FIX:      $(decision_fix "$fail_guard")"
        echo "  EVIDENCE: .kin/evidence/governance_${command}_${now}.yaml"
        echo "═══════════════════════════════════════"
        echo ""
        echo "  ⛔ No execution performed."
        echo ""
        return 1
    fi
}

# Investigation Guard — no implementation without investigation
guard_investigation() {
    local gate; gate="$(gate_current 2>/dev/null)"
    
    # Gates 6+ require investigation
    if [ "$gate" -ge 6 ]; then
        if [ -d ".kin/investigations" ] && [ -n "$(ls -A .kin/investigations 2>/dev/null)" ]; then
            echo "  ✅ Investigation complete"
            return 0
        fi
        echo "  ❌ No investigation report"
        echo "     → ai investigate services"
        return 1
    fi
    echo "  ⏭️  Investigation not required at Gate $gate"
    return 0
}
