# Description: Copy to Android clipboard
copy_main() {
    source "$SDK_ROOT/engines/clipboard/engine.sh" 2>/dev/null
    
    case "${1:-status}" in
        status)  clipboard_status ;;
        all)     clipboard_select_all ;;
        last)    clipboard_last_lines "${2:-50}" ;;
        file)    clipboard_copy_file "$2" ;;
        history) clipboard_history "${2:-10}" ;;
        restore) clipboard_last ;;
        clear)   rm -f "$(get_clipboard_dir)"/*.txt 2>/dev/null; echo "✓ Cleared" ;;
        *)
            # Treat as text to copy
            clipboard_copy "manual" "$*"
            ;;
    esac
}
main() { copy_main "$@"; }
