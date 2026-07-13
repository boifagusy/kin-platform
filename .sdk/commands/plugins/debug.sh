# Description: Debug mode — capture what you're investigating
debug_main() {
    local action="${1:-start}"
    
    case "$action" in
        start)
            local issue="${*:2}"
            [ -z "$issue" ] && issue="Debugging session"
            
            source "$SDK_ROOT/kernel/state.sh" 2>/dev/null
            
            local brick; brick="$(state_read "brick.yaml" "active_brick" 2>/dev/null | tr -d ' ')"
            local log=".kin/debug/$(date +%Y%m%d_%H%M%S).log"
            mkdir -p .kin/debug
            
            cat > "$log" << YAML
debug_session:
  started: $(date -u +%Y-%m-%dT%H:%M:%SZ)
  issue: $issue
  brick: ${brick:-unknown}
  findings: []
YAML
            
            echo ""
            echo "═══════════════════════════════════════"
            echo "  🐛 DEBUG MODE"
            echo "═══════════════════════════════════════"
            echo "  Issue:  $issue"
            echo "  Brick:  ${brick:-unknown}"
            echo "  Log:    $log"
            echo ""
            echo "  ai debug find \"what you discovered\""
            echo "  ai debug fix \"what fixed it\""
            echo "  ai debug done"
            echo "═══════════════════════════════════════"
            ;;
            
        find)
            local finding="$*"
            [ -z "$finding" ] && { echo "Usage: ai debug find \"what did you discover?\""; return 1; }
            
            local latest; latest=$(ls -t .kin/debug/*.log 2>/dev/null | head -1)
            if [ -f "$latest" ]; then
                echo "  - $(date +%H:%M:%S): $finding" >> "$latest"
            fi
            
            source "$SDK_ROOT/engines/clipboard/engine.sh" 2>/dev/null
            _clipboard_save_copy "$finding" "debug_find" 2>/dev/null
            
            echo "🔍 Finding: $finding"
            echo "   Saved + copied to clipboard"
            ;;
            
        fix)
            local fix="$*"
            [ -z "$fix" ] && { echo "Usage: ai debug fix \"what was the solution?\""; return 1; }
            
            local latest; latest=$(ls -t .kin/debug/*.log 2>/dev/null | head -1)
            if [ -f "$latest" ]; then
                echo "  - $(date +%H:%M:%S): FIX: $fix" >> "$latest"
            fi
            
            echo "✅ Fix: $fix"
            echo "   Saved to debug log"
            ;;
            
        done)
            local latest; latest=$(ls -t .kin/debug/*.log 2>/dev/null | head -1)
            if [ -f "$latest" ]; then
                echo "completed: $(date -u +%Y-%m-%dT%H:%M:%SZ)" >> "$latest"
                echo ""
                echo "═══════════════════════════════════════"
                echo "  DEBUG SESSION COMPLETE"
                echo "═══════════════════════════════════════"
                echo ""
                cat "$latest"
            fi
            ;;
            
        *)
            echo "Usage:"
            echo "  ai debug start \"issue\"    Begin debugging"
            echo "  ai debug find \"discovery\"  Log a finding"
            echo "  ai debug fix \"solution\"    Log the fix"
            echo "  ai debug done              End session + show log"
            ;;
    esac
}
main() { debug_main "$@"; }
