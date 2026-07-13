# Description: Project Resume — every AI's first command
resume_main() {
    source "$SDK_ROOT/kernel/state.sh" 2>/dev/null
    source "$SDK_ROOT/engines/gate/engine.sh" 2>/dev/null
    
    local gate=$(state_read "gate.yaml" "current" 2>/dev/null | tr -d ' ')
    local gate_name=$(gate_name "${gate:-0}" 2>/dev/null)
    local brick=$(state_read "brick.yaml" "active_brick" 2>/dev/null | tr -d ' ')
    local task=$(grep "name:" .kin/state/task.yaml 2>/dev/null | sed 's/.*: //')
    local role=$(state_read "ai.yaml" "active_role" 2>/dev/null | tr -d ' ')
    local certs=$(ls .kin/certifications/*.yaml 2>/dev/null | wc -l | tr -d ' ')
    local contracts=$(find .kin/contracts -name "*.json" 2>/dev/null | wc -l | tr -d ' ')
    
    echo ""
    echo "═══════════════════════════════════════"
    echo "  PROJECT RESUME — KIN"
    echo "═══════════════════════════════════════"
    echo ""
    echo "  Gate:         ${gate:-0} — ${gate_name:-Bootstrap}"
    echo "  Brick:        ${brick:-none}"
    echo "  Task:         ${task:-none}"
    echo "  Role:         ${role:-unassigned}"
    echo ""
    echo "  Contracts:    ${contracts} verified"
    echo "  Certifications: ${certs}"
    echo ""
    echo "  Organization:"
    echo "  EM: Idowu | Lead: ChatGPT | AI: Claude"
    echo "  OS: Engineering OS v3.3"
    echo ""
    echo "  NEXT: Awaiting Engineering Manager"
    echo "═══════════════════════════════════════"
}
main() { resume_main "$@"; }
