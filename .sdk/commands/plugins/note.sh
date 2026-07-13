# Description: Quick note — capture a bug or observation
note_main() {
    local note="$*"
    [ -z "$note" ] && { echo "Usage: ai note \"what did you find?\""; return 1; }
    
    source "$SDK_ROOT/engines/clipboard/engine.sh" 2>/dev/null
    
    local brick; brick="$(state_read "brick.yaml" "active_brick" 2>/dev/null | tr -d ' ')"
    local notes_dir=".kin/notes"
    mkdir -p "$notes_dir"
    
    local ts; ts="$(date +%Y%m%d_%H%M%S)"
    echo "[$(date)] $note (brick: ${brick:-none})" >> "$notes_dir/log.txt"
    
    # Also copy to clipboard
    _clipboard_save_copy "$note" "note" 2>/dev/null
    
    echo "📝 Noted: $note"
    echo "   Brick: ${brick:-none}"
    echo "   Saved + copied to clipboard"
}
main() { note_main "$@"; }
