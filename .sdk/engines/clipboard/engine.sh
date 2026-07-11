#!/data/data/com.termux/files/usr/bin/bash

# Clipboard Engine — argument-based only
[ -n "$SDK_ROOT" ] && KERNEL_DIR="$SDK_ROOT/kernel" || KERNEL_DIR=".sdk/kernel"

source "$KERNEL_DIR/clipboard.sh" 2>/dev/null

get_clipboard_dir()  { echo ".kin/history/clipboard"; }
get_clipboard_config() { echo ".kin/config/clipboard.yaml"; }

clipboard_init_config() {
    local config; config="$(get_clipboard_config)"
    [ -f "$config" ] && return
    mkdir -p "$(dirname "$config")"
    echo "clipboard:" > "$config"
    echo "  enabled: true" >> "$config"
    echo "  max_history: 50" >> "$config"
}

clipboard_is_enabled() {
    grep -q "enabled: true" "$(get_clipboard_config)" 2>/dev/null
}

# THE ONLY COPY FUNCTION — takes content as argument
clipboard_copy() {
    local content="$2"
    [ -z "$content" ] && { echo "Usage: ai clip copy \"text\""; return 1; }
    
    # Save history
    local dir; dir="$(get_clipboard_dir)"; mkdir -p "$dir"
    local ts; ts="$(date +%Y%m%d_%H%M%S)"
    echo "$content" > "$dir/${ts}_${1:-manual}.txt"
    
    # System clipboard via temp file
    local tmp="$HOME/.clip_$$"
    echo "$content" > "$tmp"
    termux-clipboard-set < "$tmp" 2>/dev/null
    rm -f "$tmp"
    
    echo "✓ Copied — ${ts}"
}

clipboard_history() {
    local dir; dir="$(get_clipboard_dir)"
    ls -1t "$dir"/*.txt 2>/dev/null | head -"${1:-10}" | while read f; do
        echo "  $(basename "$f" .txt) ($(wc -c < "$f" | tr -d ' ')B)"
    done
}

clipboard_last() {
    local dir; dir="$(get_clipboard_dir)"
    local last; last=$(ls -1t "$dir"/*.txt 2>/dev/null | head -1)
    [ -f "$last" ] && cat "$last" || echo "No history"
}

clipboard_status() {
    local count
    count=$(ls -1 "$(get_clipboard_dir)"/*.txt 2>/dev/null | wc -l | tr -d ' ')
    echo "✓ Clipboard active | History: ${count:-0}"
}
