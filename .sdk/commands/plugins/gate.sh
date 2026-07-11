# Description: Gate management and progression
# Requires: state

gate_main() {
    local action="${1:-status}"
    
    # SDK_ROOT is set by the main ai script
    local gate_engine="$SDK_ROOT/engines/gate/engine.sh"
    
    if [ ! -f "$gate_engine" ]; then
        echo "Gate engine not found at $gate_engine"
        return 1
    fi
    
    source "$gate_engine" 2>/dev/null || {
        echo "Failed to load gate engine"
        return 1
    }
    
    case "$action" in
        status)   gate_info ;;
        list)     gate_list ;;
        verify)   gate_verify ;;
        advance)  gate_advance ;;
        goto)     gate_goto "${2:-}" ;;
        block)    gate_block "${2:-manual block}" ;;
        unblock)  gate_unblock ;;
        init)     gate_init ;;
        *)
            echo "Usage: ai gate [status|list|verify|advance|goto|block|unblock|init]"
            echo ""
            echo "Current gate:"
            gate_info
            ;;
    esac
}

main() { gate_main "$@"; }
