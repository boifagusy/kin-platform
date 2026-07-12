#!/data/data/com.termux/files/usr/bin/bash

[ -n "$SDK_ROOT" ] && KERNEL_DIR="$SDK_ROOT/kernel" || KERNEL_DIR=".sdk/kernel"
source "$KERNEL_DIR/clipboard.sh" 2>/dev/null

get_clipboard_dir()  { echo ".kin/history/clipboard"; }

# Main copy — saves screen + copies to clipboard
clipboard_copy() {
    local content="$2"
    
    # If content provided, copy it directly
    if [ -n "$content" ]; then
        _clipboard_save_copy "$content" "${1:-manual}"
        return 0
    fi
    
    # No content = save everything on screen
    clipboard_save_all
}

# Internal: save and copy
_clipboard_save_copy() {
    local content="$1"
    local label="$2"
    local dir; dir="$(get_clipboard_dir)"; mkdir -p "$dir"
    local ts; ts="$(date +%Y%m%d_%H%M%S)"
    
    echo "$content" > "$dir/${ts}_${label}.txt"
    local tmp="$HOME/.clip_$$"
    echo "$content" > "$tmp"
    termux-clipboard-set < "$tmp" 2>/dev/null
    rm -f "$tmp"
    echo "✓ Copied — ${ts}"
}

# Save everything currently on screen
clipboard_save_all() {
    local save_file="$HOME/termux_screen_$(date +%Y%m%d_%H%M%S).txt"
    local all_content=""
    
    # Collect everything available
    all_content+="=== TERMUX SCREEN ===\n"
    all_content+="Date: $(date)\n"
    all_content+="Directory: $(pwd)\n"
    all_content+="Git: $(git branch --show-current 2>/dev/null || echo 'N/A')\n"
    all_content+="\n=== BASH HISTORY ===\n"
    all_content+="$(tail -500 "${HISTFILE:-$HOME/.bash_history}" 2>/dev/null)\n"
    all_content+="\n=== CLIPBOARD HISTORY ===\n"
    all_content+="$(ls -1t "$(get_clipboard_dir)"/*.txt 2>/dev/null | head -5 | while read f; do echo "  $(basename "$f" .txt)"; done)\n"
    
    # Save to file
    echo -e "$all_content" > "$save_file"
    
    # Copy to clipboard
    local tmp="$HOME/.clip_$$"
    echo -e "$all_content" > "$tmp"
    termux-clipboard-set < "$tmp" 2>/dev/null
    rm -f "$tmp"
    
    echo "✓ Screen saved: $save_file"
    echo "✓ Copied to Android clipboard"
    echo "✓ $(wc -l < "$save_file" | tr -d ' ') lines"
}

clipboard_copy_file() {
    local file="$1"
    [ ! -f "$file" ] && { echo "File not found: $file"; return 1; }
    _clipboard_save_copy "$(cat "$file")" "file"
}

clipboard_history() {
    local limit="${1:-10}" dir; dir="$(get_clipboard_dir)"
    echo "CLIPBOARD HISTORY"
    if [ -d "$dir" ]; then
        ls -1t "$dir"/*.txt 2>/dev/null | head -"$limit" | while read f; do
            echo "  $(basename "$f" .txt) ($(wc -c < "$f" | tr -d ' ')B)"
        done
    fi
}

clipboard_last() {
    local dir; dir="$(get_clipboard_dir)"
    local last; last=$(ls -1t "$dir"/*.txt 2>/dev/null | head -1)
    [ -f "$last" ] && cat "$last" || echo "No history"
}

clipboard_status() {
    local count
    count=$(ls -1 "$(get_clipboard_dir)"/*.txt 2>/dev/null | wc -l | tr -d ' ')
    local screen_files
    screen_files=$(ls -1 "$HOME/termux_screen_"*.txt 2>/dev/null | wc -l | tr -d ' ')
    echo "✓ Clipboard | History: ${count:-0} | Screens: ${screen_files:-0}"
    echo ""
    echo "  ai copy               Save screen + copy to clipboard"
    echo "  ai copy \"text\"        Copy text directly"
    echo "  ai copy file <f>      Copy file"
    echo "  echo \"text\" | ai      Pipe to clipboard"
    echo "  ai copy history       View history"
    echo "  ai copy last          Show last copy"
}
