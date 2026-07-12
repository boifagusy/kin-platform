#!/data/data/com.termux/files/usr/bin/bash

[ -n "$SDK_ROOT" ] && KERNEL_DIR="$SDK_ROOT/kernel" || KERNEL_DIR=".sdk/kernel"
source "$KERNEL_DIR/clipboard.sh" 2>/dev/null

get_clipboard_dir()  { echo ".kin/history/clipboard"; }

clipboard_copy() {
    local content="$2"
    [ -z "$content" ] && { echo "Usage: ai copy \"text\""; return 1; }
    local dir; dir="$(get_clipboard_dir)"; mkdir -p "$dir"
    local ts; ts="$(date +%Y%m%d_%H%M%S)"
    echo "$content" > "$dir/${ts}_${1:-manual}.txt"
    local tmp="$HOME/.clip_$$"
    echo "$content" > "$tmp"
    termux-clipboard-set < "$tmp" 2>/dev/null
    rm -f "$tmp"
    echo "✓ Copied — ${ts}"
}

# Copy all: read from bash history file
clipboard_select_all() {
    echo "Capturing terminal history..."
    
    local histfile="${HISTFILE:-$HOME/.bash_history}"
    local all_content=""
    
    if [ -f "$histfile" ]; then
        all_content="$(tail -200 "$histfile" 2>/dev/null)"
    fi
    
    if [ -n "$all_content" ]; then
        clipboard_copy "all" "$all_content"
        echo "✓ History copied (200 lines from $histfile)"
    else
        echo "⚠️  No history file found at $histfile"
        echo "   Enable history: echo 'export HISTFILE=\$HOME/.bash_history' >> ~/.bashrc"
    fi
}

clipboard_last_lines() {
    local lines="${1:-50}"
    local histfile="${HISTFILE:-$HOME/.bash_history}"
    
    if [ -f "$histfile" ]; then
        local content
        content="$(tail -"$lines" "$histfile" 2>/dev/null)"
        [ -n "$content" ] && clipboard_copy "last_${lines}" "$content" && echo "✓ Last $lines lines copied" || echo "⚠️  Empty"
    else
        echo "⚠️  No history file"
    fi
}

clipboard_copy_file() {
    local file="$1"
    [ ! -f "$file" ] && { echo "File not found: $file"; return 1; }
    clipboard_copy "file" "$(cat "$file")"
}

clipboard_history() {
    local limit="${1:-10}" dir; dir="$(get_clipboard_dir)"
    echo "CLIPBOARD HISTORY"
    echo "═══════════════════════════════════════"
    if [ -d "$dir" ] && [ -n "$(ls -A "$dir" 2>/dev/null)" ]; then
        ls -1t "$dir"/*.txt 2>/dev/null | head -"$limit" | while read f; do
            echo "  $(basename "$f" .txt) ($(wc -c < "$f" | tr -d ' ')B)"
        done
    else
        echo "  (empty)"
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
    echo "✓ Clipboard active | History: ${count:-0}"
}
