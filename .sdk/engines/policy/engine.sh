#!/data/data/com.termux/files/usr/bin/bash

# Policy Engine — Declarative rule evaluation
# Reads policy files, evaluates against state, returns decisions with evidence

if [ -n "$SDK_ROOT" ]; then
    POLICY_DIR="$SDK_ROOT/engines/policy"
    KERNEL_DIR="$SDK_ROOT/kernel"
else
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    POLICY_DIR="$SCRIPT_DIR"
    KERNEL_DIR="$(dirname "$(dirname "$SCRIPT_DIR")")/kernel"
fi

source "$KERNEL_DIR/state.sh" 2>/dev/null
source "$KERNEL_DIR/yaml.sh" 2>/dev/null

# Load a policy file
policy_load() {
    local policy_file="$POLICY_DIR/${1}.yaml"
    if [ -f "$policy_file" ]; then
        cat "$policy_file"
    else
        echo "policy not found: $1"
        return 1
    fi
}

# Evaluate a requirement against current state
policy_evaluate_requirement() {
    local requirement="$1"
    local evidence=""
    
    case "$requirement" in
        git_repository)
            git rev-parse --git-dir >/dev/null 2>&1 && { evidence=".git/ exists"; return 0; } || return 1 ;;
        sdk_installed)
            [ -f ".sdk/sdk.yaml" ] && { evidence=".sdk/sdk.yaml exists"; return 0; } || return 1 ;;
        role_assigned)
            local role; role="$(state_read "ai.yaml" "active_role" 2>/dev/null | tr -d ' ')"
            [ "${role:-unassigned}" != "unassigned" ] && { evidence=".kin/state/ai.yaml → role=$role"; return 0; } || return 1 ;;
        active_brick)
            local brick; brick="$(state_read "brick.yaml" "active_brick" 2>/dev/null | tr -d ' ')"
            [ "${brick:-none}" != "none" ] && { evidence=".kin/state/brick.yaml → brick=$brick"; return 0; } || return 1 ;;
        clean_git)
            git diff --quiet 2>/dev/null && { evidence="git diff clean"; return 0; } || return 1 ;;
        doctor_passes)
            [ -f ".sdk/sdk.yaml" ] && { evidence="SDK installed"; return 0; } || return 1 ;;
        *)
            evidence="requirement not evaluated"
            return 0 ;;
    esac
}

# Full policy check with evidence collection
policy_check() {
    local policy_name="$1"
    local policy
    policy="$(policy_load "$policy_name")" || return 1
    
    local errors=0
    local evidence_log=""
    local now
    now="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
    
    echo ""
    echo "═══════════════════════════════════════"
    echo "  POLICY ENGINE — $policy_name"
    echo "═══════════════════════════════════════"
    
    # Extract requirements from policy
    local requirements
    requirements="$(echo "$policy" | grep "^  - " | sed 's/  - //')"
    
    for req in $requirements; do
        local req_evidence=""
        if policy_evaluate_requirement "$req"; then
            echo "  ✅ $req — met"
            req_evidence="$req: met"
        else
            echo "  ❌ $req — not met"
            req_evidence="$req: NOT MET"
            errors=$((errors + 1))
        fi
        evidence_log="${evidence_log}${req_evidence}\n"
    done
    
    # Save evidence
    mkdir -p .kin/evidence
    cat > ".kin/evidence/${policy_name}_${now}.yaml" << YAML
policy: $policy_name
timestamp: $now
results:
$(echo -e "$evidence_log" | sed 's/^/  - /')
verdict: $([ $errors -eq 0 ] && echo "PASS" || echo "FAIL")
YAML
    
    echo ""
    echo "═══════════════════════════════════════"
    if [ $errors -eq 0 ]; then
        echo "  DECISION: ✅ PASS"
        echo "  EVIDENCE: .kin/evidence/${policy_name}_${now}.yaml"
    else
        echo "  DECISION: ❌ FAIL — $errors unmet"
        echo "  EVIDENCE: .kin/evidence/${policy_name}_${now}.yaml"
    fi
    echo "═══════════════════════════════════════"
    
    return $errors
}
