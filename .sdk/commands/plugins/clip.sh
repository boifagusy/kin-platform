# Description: Clipboard Engine
clip_main() {
    source "$SDK_ROOT/engines/clipboard/engine.sh" 2>/dev/null
    
    case "${1:-status}" in
        status)  clipboard_status ;;
        copy)    clipboard_copy "manual" "$2" ;;
        history) clipboard_history "${2:-10}" ;;
        last)    clipboard_last ;;
        *)       echo "Usage: ai clip [copy|history|last|status]"
                 clipboard_status ;;
    esac
}
main() { clip_main "$@"; }
