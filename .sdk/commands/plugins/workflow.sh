# Description: Workflow management
# Requires: state gate

workflow_main() {
    local action="${1:-status}"
    
    local gate_engine="$SDK_ROOT/engines/gate/engine.sh"
    local workflow_engine="$SDK_ROOT/engines/workflow/engine.sh"
    
    if [ ! -f "$gate_engine" ]; then
        echo "Gate engine not found"
        return 1
    fi
    
    if [ ! -f "$workflow_engine" ]; then
        echo "Workflow engine not found"
        return 1
    fi
    
    # Source gate engine first (dependency)
    source "$gate_engine" 2>/dev/null
    source "$workflow_engine" 2>/dev/null || {
        echo "Failed to load workflow engine"
        return 1
    }
    
    case "$action" in
        status)  workflow_status ;;
        next)    workflow_next ;;
        step)    workflow_step "${2:-}" ;;
        sync)    workflow_sync ;;
        approval) workflow_approval_needed ;;
        *)
            echo "Usage: ai workflow [status|next|step|sync|approval]"
            workflow_status
            ;;
    esac
}

main() { workflow_main "$@"; }
