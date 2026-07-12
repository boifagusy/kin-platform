# Description: Help system
help_main() {
    local action="${1:-main}"
    source "$SDK_ROOT/engines/help/engine.sh" 2>/dev/null
    
    case "$action" in
        main)      engine_help_main ;;
        tutorial)  help_tutorial "${2:-1}" ;;
        examples)  help_examples "${2:-}" ;;
        explain)   help_explain "${2:-}" ;;
        *)         help_topic "$action" ;;
    esac
}
main() { help_main "$@"; }
