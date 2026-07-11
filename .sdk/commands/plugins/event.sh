# Description: Event management
# Requires: state

event_main() {
    local action="${1:-list}"
    
    local event_engine="$SDK_ROOT/engines/event/engine.sh"
    
    [ -f "$event_engine" ] || { echo "Event engine not found"; return 1; }
    
    source "$event_engine" 2>/dev/null || { echo "Failed to load event engine"; return 1; }
    
    case "$action" in
        list)    event_list "${2:-20}" ;;
        query)   event_query "${2:-}" ;;
        publish) event_publish "${2:-}" "${3:-}" "${4:-}" ;;
        count)   event_count ;;
        prune)   event_prune "${2:-100}" ;;
        init)    event_init ;;
        *)
            echo "Usage: ai event [list|query|publish|count|prune|init]"
            event_list 5
            ;;
    esac
}

main() { event_main "$@"; }
