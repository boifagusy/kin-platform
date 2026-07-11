#!/data/data/com.termux/files/usr/bin/bash

# Clipboard Engine — Kernel Service
# Does NOT know about project layout. That's the engine's job.

# Platform detection
clipboard_detect_platform() {
    if [ -d "/data/data/com.termux" ]; then echo "termux"
    elif command -v xclip >/dev/null 2>&1; then echo "linux"
    elif command -v pbcopy >/dev/null 2>&1; then echo "macos"
    elif command -v clip.exe >/dev/null 2>&1; then echo "windows"
    else echo "unknown"; fi
}

# Service available?
clipboard_available() {
    local p; p="$(clipboard_detect_platform)"
    case "$p" in
        termux) command -v termux-clipboard-set >/dev/null 2>&1 ;;
        linux)  command -v xclip >/dev/null 2>&1 ;;
        macos)  command -v pbcopy >/dev/null 2>&1 ;;
        windows) command -v clip.exe >/dev/null 2>&1 ;;
        *) return 1 ;;
    esac
}

# Raw copy to system clipboard (no history, no config — pure function)
clipboard_write() {
    local p; p="$(clipboard_detect_platform)"
    case "$p" in
        termux) termux-clipboard-set ;;
        linux)  xclip -selection clipboard ;;
        macos)  pbcopy ;;
        windows) clip.exe ;;
        *) return 1 ;;
    esac
}

# Strip ANSI codes
clipboard_strip_ansi() {
    sed 's/\x1b\[[0-9;]*m//g'
}

# Notify if available
clipboard_notify() {
    local msg="${1:-Copied to clipboard}"
    termux-notification -t "Engineering OS" -c "$msg" 2>/dev/null || true
}
