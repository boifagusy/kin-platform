# Description: Help system — reference, tutorial, examples, explain
# Requires: state gate

help_main() {
    local action="${1:-main}"
    
    local help_engine="$SDK_ROOT/engines/help/engine.sh"
    [ -f "$help_engine" ] || { echo "Help engine not found"; return 1; }
    source "$help_engine" 2>/dev/null || { echo "Failed to load help"; return 1; }
    
    case "$action" in
        main)      help_main ;;
        tutorial)  help_tutorial "${2:-1}" ;;
        examples)  help_examples "${2:-}" ;;
        explain)   help_explain "${2:-}" ;;
        *)         help_topic "$action" ;;
    esac
}

main() { help_main "$@"; }
