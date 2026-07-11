# Description: Knowledge base management
# Requires: state

knowledge_main() {
    local action="${1:-list}"
    
    local knowledge_engine="$SDK_ROOT/engines/knowledge/engine.sh"
    
    [ -f "$knowledge_engine" ] || { echo "Knowledge engine not found"; return 1; }
    source "$knowledge_engine" 2>/dev/null || { echo "Failed to load knowledge engine"; return 1; }
    
    case "$action" in
        list)    knowledge_list "${2:-all}" ;;
        search)  knowledge_search "${2:-}" ;;
        suggest) knowledge_suggest "${2:-}" ;;
        count)   knowledge_count ;;
        add-bug) knowledge_add_bug "${2:-}" "${3:-}" "${4:-}" "${5:-}" ;;
        add-pattern) knowledge_add_pattern "${2:-}" "${3:-}" "${4:-}" ;;
        add-lesson)  knowledge_add_lesson "${2:-}" "${3:-}" "${4:-}" ;;
        *)
            echo "Usage: ai knowledge [list|search|suggest|count|add-bug|add-pattern|add-lesson]"
            knowledge_list
            ;;
    esac
}

main() { knowledge_main "$@"; }
