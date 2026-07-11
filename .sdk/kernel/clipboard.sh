#!/data/data/com.termux/files/usr/bin/bash

# Clipboard Engine — Kernel Service
# Termux requires file-based input for termux-clipboard-set

clipboard_detect_platform() {
    [ -d "/data/data/com.termux" ] && echo "termux" && return
    command -v xclip >/dev/null 2>&1 && echo "linux" && return
    command -v pbcopy >/dev/null 2>&1 && echo "macos" && return
    command -v clip.exe >/dev/null 2>&1 && echo "windows" && return
    echo "unknown"
}

clipboard_available() {
    command -v termux-clipboard-set >/dev/null 2>&1
}

# Write content to system clipboard
# Takes content as argument (not stdin) to avoid pipe issues
clipboard_write() {
    local content="$1"
    local tmp="$HOME/.clipboard_tmp_$$"
    echo "$content" > "$tmp"
    termux-clipboard-set < "$tmp" 2>/dev/null
    local rc=$?
    rm -f "$tmp"
    return $rc
}

clipboard_strip_ansi() {
    sed 's/\x1b\[[0-9;]*m//g'
}

clipboard_notify() {
    termux-notification -t "Engineering OS" -c "${1:-Copied to clipboard}" 2>/dev/null || true
}
