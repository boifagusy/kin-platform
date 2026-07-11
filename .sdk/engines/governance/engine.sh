#!/data/data/com.termux/files/usr/bin/bash

# GOVERNANCE ENGINE v2.0 — Execution Levels + Decision Codes

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
source "$KERNEL_DIR/yaml.sh" 2>/dev/null
source "$ENGINES_DIR/gate/engine.sh" 2>/dev/null
source "$GOV_DIR/levels.sh" 2>/dev/null
source "$GOV_DIR/codes.sh" 2>/dev/null

governance_check() {
    local command="${1:-unknown}"
    local target="${2:-}"
    local level
    level="$(governance_level "$command")"
    local level_name
    level_name="$(governance_level_name "$level")"
    
    # Gather state
    local project session role gate brick blocked
    project="$(get_project_root 2>/dev/null | xargs basename 2>/dev/null)"
    session="$(state_read "session.yaml" "status" 2>/dev/null | tr -d ' ')"
    role="$(state_read "ai.yaml" "active_role" 2>/dev/null | tr -d ' ')"
    gate="$(gate_current 2>/dev/null)"
    brick="$(state_read "brick.yaml" "active_brick" 2>/dev/null | tr -d ' ')"
    blocked="$(state_read "gate.yaml" "blocked" 2>/dev/null | tr -d ' ')"
    
    # Level 0 — Skip entirely
    if [ "$level" -eq 0 ]; then
        return 0
    fi
    
    echo ""
    echo "═══════════════════════════════════════"
    echo "  GOVERNANCE ENGINE v2.0"
    echo "  Level $level — $level_name"
    echo "═══════════════════════════════════════"
    echo "  ${project:-?} | ${role:-?} | Gate $gate — $(gate_name "$gate" 2>/dev/null) | ${brick:-no brick}"
    echo "  Command: $command ${target:+→ $target}"
    echo ""
    
    local errors=0
    local fail_guard=""
    local fail_code=""
    
    # ── Level 1+: Session ──
    if [ "$session" != "active" ]; then
        fail_guard="session"
        errors=$((errors + 1))
    else
        echo "  ✅ Session active"
    fi
    
    # ── Level 1+: Role ──
    if [ "${role:-unassigned}" = "unassigned" ]; then
        [ -z "$fail_guard" ] && fail_guard="role"
        errors=$((errors + 1))
    else
        echo "  ✅ Role: $role"
    fi
    
    # ── Level 2+: Gate ──
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
            [ -z "$fail_guard" ] && fail_guard="gate"
            errors=$((errors + 1))
        else
            echo "  ✅ Gate $gate — $(gate_name "$gate" 2>/dev/null)"
        fi
    fi
    
    # ── Level 3+: Brick ──
    if [ "$level" -ge 3 ]; then
        local req_gate=6
        case "$command" in
            test|verify_test) req_gate=7 ;;
            integrate|merge) req_gate=8 ;;
        esac
        
        if [ "$gate" -ge "$req_gate" ]; then
            if [ "${brick:-none}" = "none" ]; then
                [ -z "$fail_guard" ] && fail_guard="brick"
                errors=$((errors + 1))
            elif [ ! -d "bricks/$brick" ]; then
                [ -z "$fail_guard" ] && fail_guard="brick"
                errors=$((errors + 1))
            else
                echo "  ✅ Brick: $brick"
            fi
        fi
    fi
    
    # ── Level 3+: Approval (Gates 3, 11) ──
    if [ "$level" -ge 3 ]; then
        if [ "$gate" -eq 3 ] || [ "$gate" -eq 11 ]; then
            [ -z "$fail_guard" ] && fail_guard="approval"
            errors=$((errors + 1))
        fi
    fi
    
    # ── Level 3+: Blocked ──
    if [ "$level" -ge 3 ] && [ "$blocked" = "true" ]; then
        [ -z "$fail_guard" ] && fail_guard="blocked"
        errors=$((errors + 1))
    fi
    
    # ── Level 4: Git Clean + Release ──
    if [ "$level" -ge 4 ]; then
        if ! git diff --quiet 2>/dev/null; then
            [ -z "$fail_guard" ] && fail_guard="git_clean"
            errors=$((errors + 1))
        fi
    fi
    
    # ── VERDICT with Decision Code ──
    echo ""
    echo "═══════════════════════════════════════"
    
    if [ $errors -eq 0 ]; then
        echo "  DECISION: ✅ PASS"
        echo "  LEVEL:    $level — $level_name"
        echo "═══════════════════════════════════════"
        echo ""
        return 0
    else
        fail_code="$(decision_code "$fail_guard")"
        echo "  DECISION: ❌ BLOCKED"
        echo "  CODE:     $fail_code"
        echo "  REASON:   $(decision_reason "$fail_guard")"
        echo "  LEVEL:    $level — $level_name"
        echo "═══════════════════════════════════════"
        echo ""
        echo "  FIX: $(decision_fix "$fail_guard")"
        echo ""
        echo "  ⛔ No execution performed."
        echo ""
        return 1
    fi
}
