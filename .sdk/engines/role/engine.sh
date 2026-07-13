#!/data/data/com.termux/files/usr/bin/bash

if [ -n "$SDK_ROOT" ]; then KERNEL_DIR="$SDK_ROOT/kernel"
else SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"; KERNEL_DIR="$(dirname "$(dirname "$SCRIPT_DIR")")/kernel"
fi
source "$KERNEL_DIR/state.sh" 2>/dev/null

role_detect() {
    local dir=$(pwd)
    if echo "$dir" | grep -qE "(tests?|__tests__|spec)/"; then echo "tester"
    elif echo "$dir" | grep -qE "src/(screens|components|pages)/"; then echo "frontend"
    elif echo "$dir" | grep -qE "app/(Services|Http/Controllers|Models)/"; then echo "backend"
    else echo "architect"; fi
}

role_commands() {
    case "${1:-architect}" in
        architect) echo "ai discovery | ai project | ai gate advance" ;;
        backend)   echo "ai contract verify | ai investigate services" ;;
        frontend)  echo "npm run dev | ai debug start \"UI issue\"" ;;
        debugger)  echo "ai debug start | tail -f logs | grep -rn error ." ;;
        tester)    echo "php artisan test | ai note \"result\"" ;;
        reviewer)  echo "ai audit | git diff | ai gate verify" ;;
        release)   echo "ai release status | ai release create" ;;
    esac
}

role_set() {
    local role="${1:-architect}"
    state_write "ai.yaml" "active_role" "$role" 2>/dev/null
    state_write "session.yaml" "role" "$role" 2>/dev/null
    echo "✅ Role: $role"
    echo "   $(role_commands "$role")"
}

role_auto() {
    local detected=$(role_detect)
    local current=$(state_read "ai.yaml" "active_role" 2>/dev/null | tr -d ' ')
    [ "$detected" != "$current" ] && echo "💡 Detected: $detected — ai role set $detected"
}

role_status() {
    local role=$(state_read "ai.yaml" "active_role" 2>/dev/null | tr -d ' ')
    echo "Role: ${role:-unassigned}"
    echo "Tools: $(role_commands "${role:-architect}")"
}
