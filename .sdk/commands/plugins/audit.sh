# Description: Audit trail management
# Requires: state

audit_main() {
    local action="${1:-list}"
    
    local audit_engine="$SDK_ROOT/engines/audit/engine.sh"
    
    [ -f "$audit_engine" ] || { echo "Audit engine not found"; return 1; }
    source "$audit_engine" 2>/dev/null || { echo "Failed to load audit engine"; return 1; }
    
    case "$action" in
        list)    audit_list "${2:-20}" ;;
        query)   audit_query "${2:-}" "${3:-}" ;;
        count)   audit_count ;;
        verify)  audit_verify ;;
        *)
            echo "Usage: ai audit [list|query|count|verify]"
            audit_list 5
            ;;
    esac
}

main() { audit_main "$@"; }
