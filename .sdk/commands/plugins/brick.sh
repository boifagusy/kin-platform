# Description: Brick management
# Requires: state

brick_main() {
    local action="${1:-list}"
    
    local brick_engine="$SDK_ROOT/engines/brick/engine.sh"
    
    if [ ! -f "$brick_engine" ]; then
        echo "Brick engine not found"
        return 1
    fi
    
    source "$brick_engine" 2>/dev/null || {
        echo "Failed to load brick engine"
        return 1
    }
    
    case "$action" in
        list)    brick_list ;;
        create)  brick_create "${2:-}" ;;
        info)    brick_info "${2:-}" ;;
        status)  brick_status "${2:-}" "${3:-}" ;;
        lock)    brick_lock "${2:-}" "${3:-}" ;;
        unlock)  brick_unlock "${2:-}" ;;
        depend)  brick_depend "${2:-}" "${3:-}" ;;
        validate) brick_validate "${2:-}" ;;
        *)
            echo "Usage: ai brick [list|create|info|status|lock|unlock|depend|validate]"
            echo ""
            brick_list
            ;;
    esac
}

main() { brick_main "$@"; }
