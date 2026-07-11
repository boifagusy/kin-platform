# Description: Clipboard Engine
clip_main() {
    local engine="$SDK_ROOT/engines/clipboard/engine.sh"
    [ -f "$engine" ] || { echo "Clipboard engine not found"; return 1; }
    source "$engine" 2>/dev/null
    
    local action="${1:-status}"; shift 2>/dev/null || true
    case "$action" in
        status)   clipboard_status ;;
        copy)     clipboard_copy "manual" "$*" ;;
        file)     clipboard_copy_file "${1:-}" ;;
        history)  clipboard_history "${1:-10}" ;;
        last)     clipboard_last ;;
        restore)  clipboard_restore "${1:-1}" ;;
        clear)    clipboard_clear ;;
        enable)   clipboard_enable ;;
        disable)  clipboard_disable ;;
        *)        echo "Usage: ai clip [status|copy|file|history|last|restore|clear|enable|disable]" ;;
    esac
}
main() { clip_main "$@"; }
