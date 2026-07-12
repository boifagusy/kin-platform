#!/data/data/com.termux/files/usr/bin/bash

[ -n "$SDK_ROOT" ] && KERNEL_DIR="$SDK_ROOT/kernel" || KERNEL_DIR=".sdk/kernel"
source "$KERNEL_DIR/clipboard.sh" 2>/dev/null

get_clipboard_dir()  { echo ".kin/history/clipboard"; }
get_project_root()   { git rev-parse --show-toplevel 2>/dev/null || pwd; }

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

clipboard_save_all() {
    local root; root="$(get_project_root)"
    local project; project="$(basename "$root")"
    local branch; branch="$(git -C "$root" branch --show-current 2>/dev/null || echo 'N/A')"
    local save_file="$HOME/termux_screen_$(date +%Y%m%d_%H%M%S).txt"
    
    {
        echo "=== TERMUX SCREEN ==="
        echo "Date: $(date)"
        echo "Project: ${project}"
        echo "Directory: ${root}"
        echo "Git: ${branch}"
        echo ""
        echo "=== BASH HISTORY ==="
        tail -500 "${HISTFILE:-$HOME/.bash_history}" 2>/dev/null
        echo ""
        echo "=== CLIPBOARD HISTORY ==="
        ls -1t "$(get_clipboard_dir)"/*.txt 2>/dev/null | head -5 | while read f; do
            echo "  $(basename "$f" .txt)"
        done
    } > "$save_file"
    
    _clipboard_save_copy "$(cat "$save_file")" "screen"
    echo "✓ Screen saved: $save_file"
    echo "✓ $(wc -l < "$save_file" | tr -d ' ') lines"
}

clipboard_copy() {
    local content="$2"
    if [ -z "$content" ]; then
        clipboard_save_all
    else
        _clipboard_save_copy "$content" "${1:-manual}"
    fi
}

clipboard_select_all() {
    local histfile="${HISTFILE:-$HOME/.bash_history}"
    if [ -f "$histfile" ]; then
        _clipboard_save_copy "$(tail -500 "$histfile" 2>/dev/null)" "history"
        echo "✓ 500 lines copied"
    else
        echo "No history. Run: touch ~/.bash_history"
    fi
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
    local count; count=$(ls -1 "$(get_clipboard_dir)"/*.txt 2>/dev/null | wc -l | tr -d ' ')
    echo "✓ Clipboard | History: ${count:-0}"
    echo "  ai copy               Save screen + copy"
    echo "  ai copy \"text\"        Copy text"
    echo "  ai copy file <f>      Copy file"
    echo "  echo \"text\" | ai      Pipe to clipboard"
}
