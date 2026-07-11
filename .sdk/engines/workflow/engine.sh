#!/data/data/com.termux/files/usr/bin/bash

if [ -n "$SDK_ROOT" ]; then
    KERNEL_DIR="$SDK_ROOT/kernel"
    ENGINES_DIR="$SDK_ROOT/engines"
else
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    ENGINES_DIR="$(dirname "$SCRIPT_DIR")"
    KERNEL_DIR="$(dirname "$ENGINES_DIR")/kernel"
fi

source "$KERNEL_DIR/common.sh" 2>/dev/null || true
source "$KERNEL_DIR/errors.sh" 2>/dev/null || true
source "$KERNEL_DIR/state.sh" 2>/dev/null || true
source "$KERNEL_DIR/yaml.sh" 2>/dev/null || true
source "$ENGINES_DIR/gate/engine.sh" 2>/dev/null || true

# Compute next action based on current state
workflow_next() {
    local role gate brick git_status docs_status blocked
    
    role="$(state_read "ai.yaml" "active_role" 2>/dev/null | tr -d ' ')"
    gate="$(gate_current)"
    brick="$(state_read "brick.yaml" "active_brick" 2>/dev/null | tr -d ' ')"
    git_status="$(state_read "github.yaml" "status" 2>/dev/null | tr -d ' ')"
    blocked="$(state_read "gate.yaml" "blocked" 2>/dev/null | tr -d ' ')"
    
    echo "WORKFLOW ANALYSIS"
    echo "═══════════════════════════════════════"
    echo "  Role:     ${role:-unassigned}"
    echo "  Gate:     $gate - $(gate_name "$gate")"
    echo "  Brick:    ${brick:-none}"
    echo "  Git:      ${git_status:-0} changes"
    echo "  Blocked:  ${blocked:-false}"
    echo ""
    
    # Check if blocked
    if [ "$blocked" = "true" ]; then
        local reason
        reason="$(state_read "gate.yaml" "blocked_reason" 2>/dev/null)"
        echo "▶️  NEXT: Resolve block - $reason"
        return 0
    fi
    
    # Compute next action based on gate and role
    case "$gate" in
        0)
            case "${role:-unassigned}" in
                unassigned) echo "▶️  NEXT: ai session start && ai role set <role>" ;;
                *) echo "▶️  NEXT: ai doctor && ai gate verify" ;;
            esac ;;
        1|2|3|4|5)
            echo "▶️  NEXT: Create required documents for Gate $gate"
            gate_exit_requirements "$gate" | grep "documents:" | sed 's/.*\[//;s/\]//' | while IFS=',' read -ra docs; do
                for doc in "${docs[@]}"; do
                    echo "     - Create $(echo "$doc" | tr -d ' ')"
                done
            done ;;
        6)
            if [ "${brick:-none}" = "none" ]; then
                echo "▶️  NEXT: ai brick create <name>"
            else
                echo "▶️  NEXT: Implement brick: $brick"
            fi ;;
        7)
            echo "▶️  NEXT: ai test brick ${brick:-all}" ;;
        8)
            echo "▶️  NEXT: ai test integration" ;;
        9)
            echo "▶️  NEXT: ai test system" ;;
        10)
            echo "▶️  NEXT: Complete production checklist" ;;
        11)
            echo "▶️  NEXT: Create release" ;;
    esac
    
    # Git warnings
    if [ "${git_status:-0}" -gt 5 ]; then
        echo ""
        echo "⚠️  WARNING: $git_status uncommitted changes"
        echo "   Consider: git add -A && git commit -m '...'"
    fi
    
    # Documentation reminder
    if [ -f "docs/governance/AGENT_LOG.md" ]; then
        local last_log
        last_log="$(grep -c "^##" docs/governance/AGENT_LOG.md 2>/dev/null || echo 0)"
        if [ "${last_log:-0}" -eq 0 ]; then
            echo ""
            echo "⚠️  AGENT_LOG.md may need updating"
        fi
    fi
}

# Show workflow status
workflow_status() {
    local role gate brick stage
    role="$(state_read "ai.yaml" "active_role" 2>/dev/null | tr -d ' ')"
    gate="$(gate_current)"
    brick="$(state_read "brick.yaml" "active_brick" 2>/dev/null | tr -d ' ')"
    stage="$(state_read "ai.yaml" "waiting_for" 2>/dev/null | tr -d ' ')"
    
    echo "WORKFLOW STATUS"
    echo "═══════════════════════════════════════"
    echo "  Role:       ${role:-unassigned}"
    echo "  Gate:       $gate ($(gate_name "$gate"))"
    echo "  Brick:      ${brick:-none}"
    echo "  Waiting:    ${stage:-nothing}"
    echo "  Git branch: $(git branch --show-current 2>/dev/null || echo 'unknown')"
    echo "  Git status: $(git status --porcelain 2>/dev/null | wc -l | tr -d ' ') files"
    echo "  Session:    $(state_read "session.yaml" "status" 2>/dev/null | tr -d ' ')"
}

# Update workflow step
workflow_step() {
    local step="$1"
    state_write "ai.yaml" "waiting_for" "$step"
    state_write "ai.yaml" "last_action" "$step"
    echo "Workflow step: $step"
}

# Check approval requirements
workflow_approval_needed() {
    local gate
    gate="$(gate_current)"
    
    case "$gate" in
        3|11) 
            echo "true"
            echo "Gate $gate requires Engineering Manager approval" ;;
        *) echo "false" ;;
    esac
}

# Sync workflow with governance documents
workflow_sync() {
    echo "Syncing workflow with governance..."
    
    # Read governance documents
    if [ -f "docs/governance/KIN_ENGINEERING_OS.md" ]; then
        echo "  ✅ KEOS found"
    else
        echo "  ⚠️  KEOS not found"
    fi
    
    if [ -f "docs/governance/AI_CONTRACT.yaml" ]; then
        echo "  ✅ AI Contract found"
    else
        echo "  ⚠️  AI Contract not found"
    fi
    
    # Update gate state based on governance
    local gate
    gate="$(gate_current)"
    state_write "ai.yaml" "current_gate" "$gate"
    
    echo "Workflow synced to Gate $gate"
}
