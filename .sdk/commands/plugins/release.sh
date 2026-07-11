# Description: Release management
# Requires: state git

release_main() {
    local action="${1:-status}"
    
    local release_engine="$SDK_ROOT/engines/release/engine.sh"
    
    [ -f "$release_engine" ] || { echo "Release engine not found"; return 1; }
    source "$release_engine" 2>/dev/null || { echo "Failed to load release engine"; return 1; }
    
    case "$action" in
        status)   
            echo "Current version: $(release_current_version)"
            echo "Suggested next: patch=$(release_suggest_version patch) minor=$(release_suggest_version minor) major=$(release_suggest_version major)"
            ;;
        suggest)  release_suggest_version "${2:-patch}" ;;
        checklist) release_checklist "$(release_current_version)" ;;
        changelog) release_changelog "$(release_current_version)" ;;
        create)   release_create "${2:-}" "${3:-patch}" ;;
        verify)   release_verify "${2:-$(release_current_version)}" ;;
        rollback) release_rollback "${2:-}" ;;
        *)
            echo "Usage: ai release [status|suggest|checklist|changelog|create|verify|rollback]"
            echo "Current: $(release_current_version)"
            ;;
    esac
}

main() { release_main "$@"; }
