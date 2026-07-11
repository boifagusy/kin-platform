#!/data/data/com.termux/files/usr/bin/bash

# Governance Engine — Automatic Pre-Command Enforcement
# No command bypasses this. No code generated without passing.

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

# ── Governance Check — runs before any engineering command ──
governance_check() {
    local command="${1:-unknown}"
    local target="${2:-}"
    local errors=0
    local blocks=""
    
    # Gather state
    local project session role gate gate_name brick locked blocked health
    project="$(get_project_root 2>/dev/null | xargs basename)"
    session="$(state_read "session.yaml" "status" 2>/dev/null | tr -d ' ')"
    role="$(state_read "ai.yaml" "active_role" 2>/dev/null | tr -d ' ')"
    gate="$(gate_current 2>/dev/null)"
    gate_name="$(gate_name "$gate" 2>/dev/null)"
    brick="$(state_read "brick.yaml" "active_brick" 2>/dev/null | tr -d ' ')"
    blocked="$(state_read "gate.yaml" "blocked" 2>/dev/null | tr -d ' ')"
    health="$(state_read "ai.yaml" "health" 2>/dev/null | tr -d ' ')"

    echo ""
    echo "═══════════════════════════════════════"
    echo "  GOVERNANCE ENGINE"
    echo "═══════════════════════════════════════"
    echo "  Request: $command ${target:+→ $target}"
    echo ""

    # ── CHECK 1: Context ──
    echo "  CONTEXT"
    echo "  ─────────────────────────────────"
    echo "  Project:   ${project:-unknown}"
    echo "  Session:   ${session:-inactive}"
    echo "  Role:      ${role:-unassigned}"
    echo "  Gate:      $gate — $gate_name"
    echo "  Brick:     ${brick:-none}"
    
    if [ "$session" != "active" ]; then
        echo "  ❌ Session inactive"
        blocks="$blocks\n  → ai session start"
        errors=$((errors + 1))
    fi
    
    if [ "${role:-unassigned}" = "unassigned" ]; then
        echo "  ❌ No role assigned"
        blocks="$blocks\n  → ai role set <role>"
        errors=$((errors + 1))
    fi

    # ── CHECK 2: Gate Guard ──
    echo ""
    echo "  GATE GUARD"
    echo "  ─────────────────────────────────"
    
    local required_gate=0
    case "$command" in
        implement|code|develop|build|fix|modify|patch|brick)
            required_gate=6 ;;
        test|verify_test|validate)
            required_gate=7 ;;
        integrate|merge)
            required_gate=8 ;;
        release|deploy|publish)
            required_gate=11 ;;
        plan|design|architect)
            required_gate=5 ;;
        restore)
            required_gate="$gate" ;;  # Restore at current gate
        *)
            required_gate="$gate" ;;
    esac
    
    if [ "$gate" -lt "$required_gate" ]; then
        echo "  ❌ Gate $gate too early for '$command'"
        echo "     Required: Gate $required_gate — $(gate_name "$required_gate" 2>/dev/null)"
        local steps=$((required_gate - gate))
        blocks="$blocks\n  → Advance $steps gate(s): ai gate verify && ai gate advance"
        errors=$((errors + 1))
    else
        echo "  ✅ Gate $gate sufficient for '$command'"
    fi

    # ── CHECK 3: Brick Guard ──
    if [ "$required_gate" -ge 6 ]; then
        echo ""
        echo "  BRICK GUARD"
        echo "  ─────────────────────────────────"
        
        if [ "${brick:-none}" = "none" ]; then
            echo "  ❌ No active brick"
            blocks="$blocks\n  → ai brick create <name>"
            errors=$((errors + 1))
        else
            echo "  ✅ Brick: $brick"
            
            # Check if brick exists
            if [ ! -d "bricks/$brick" ]; then
                echo "  ❌ Brick '$brick' not found"
                blocks="$blocks\n  → ai brick create $brick"
                errors=$((errors + 1))
            fi
            
            # Check if locked
            if [ -f "bricks/$brick/brick.yaml" ]; then
                local brick_locked locked_by
                brick_locked="$(yaml_get_nested "bricks/$brick/brick.yaml" "brick" "locked" 2>/dev/null)"
                locked_by="$(yaml_get_nested "bricks/$brick/brick.yaml" "brick" "locked_by" 2>/dev/null)"
                
                if [ "$brick_locked" = "true" ] && [ "$locked_by" != "${role:-unknown}" ]; then
                    echo "  ⚠️  Brick locked by $locked_by"
                elif [ "$brick_locked" != "true" ]; then
                    echo "  ⚠️  Brick unlocked — ai brick lock $brick $role"
                fi
            fi
        fi
    fi

    # ── CHECK 4: Approval Guard ──
    echo ""
    echo "  APPROVAL GUARD"
    echo "  ─────────────────────────────────"
    
    if [ "$gate" -eq 3 ] || [ "$gate" -eq 11 ]; then
        echo "  ❌ Gate $gate requires Engineering Manager approval"
        blocks="$blocks\n  → Await Engineering Manager approval"
        errors=$((errors + 1))
    else
        echo "  ✅ No approval required at Gate $gate"
    fi

    # ── CHECK 5: Blocked? ──
    if [ "$blocked" = "true" ]; then
        local reason
        reason="$(state_read "gate.yaml" "blocked_reason" 2>/dev/null)"
        echo ""
        echo "  BLOCKER"
        echo "  ─────────────────────────────────"
        echo "  ❌ Gate blocked: $reason"
        blocks="$blocks\n  → Resolve: $reason"
        errors=$((errors + 1))
    fi

    # ── VERDICT ──
    echo ""
    echo "═══════════════════════════════════════"
    
    if [ $errors -eq 0 ]; then
        echo "  VERDICT: ✅ PASS"
        echo "  Proceed with: $command ${target:+$target}"
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
        echo "  ⛔ No code may be created until all checks pass."
        echo "  ⛔ Do NOT proceed with: $command ${target:+$target}"
        echo ""
        return 1
    fi
}

# ── Context header for AI responses ──
governance_context() {
    local project gate gate_name brick role
    project="$(get_project_root 2>/dev/null | xargs basename)"
    gate="$(gate_current 2>/dev/null)"
    gate_name="$(gate_name "$gate" 2>/dev/null)"
    brick="$(state_read "brick.yaml" "active_brick" 2>/dev/null | tr -d ' ')"
    role="$(state_read "ai.yaml" "active_role" 2>/dev/null | tr -d ' ')"
    
    echo "═══════════════════════════════════════"
    echo " ${project:-?} | ${role:-?} | Gate $gate — $gate_name | ${brick:-no brick}"
    echo "═══════════════════════════════════════"
}
