#!/data/data/com.termux/files/usr/bin/bash

ambient_check() {
    local dir=$(pwd)
    local root=$(git rev-parse --show-toplevel 2>/dev/null) || return 0
    local rel="${dir#$root/}"
    [ "$rel" = "$dir" ] && return 0
    
    local role="architect"
    [ -f "$root/.kin/state/ai.yaml" ] && role=$(grep "active_role:" "$root/.kin/state/ai.yaml" 2>/dev/null | sed 's/.*: //' | tr -d ' ')
    
    local name=$(basename "$dir")
    
    # Show context with role-specific guidance
    echo ""
    echo "  📍 $name | Role: $role"
    
    if echo "$rel" | grep -qE "app/(Services|Http/Controllers|Models)/"; then
        case "$role" in
            debugger)
                echo "  🐛 Debug mode"
                echo "  💡 ai debug start \"$name issue\""
                echo "  💡 tail -f storage/logs/laravel.log"
                echo "  💡 grep -rn \"error\|exception\" ."
                ;;
            backend*|architect)
                echo "  💡 ai do \"working on $name\""
                echo "  💡 ai contract verify"
                ;;
            tester)
                echo "  🧪 Test mode"
                echo "  💡 php artisan test --filter=$name"
                ;;
            *)
                echo "  💡 ai do \"working on $name\""
                ;;
        esac
    elif echo "$rel" | grep -qE "src/(screens|components)/"; then
        case "$role" in
            debugger)
                echo "  🐛 Debug mode"
                echo "  💡 ai debug start \"$name UI issue\""
                echo "  💡 npm run dev (check browser console)"
                ;;
            frontend*)
                echo "  💡 npm run dev"
                ;;
            *)
                echo "  💡 ai note \"modifying $name\""
                ;;
        esac
    fi
}

if git rev-parse --git-dir >/dev/null 2>&1; then
    ambient_check 2>/dev/null
fi
