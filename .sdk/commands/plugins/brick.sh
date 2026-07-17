brick_main() {
    source "$SDK_ROOT/kernel/state.sh" 2>/dev/null
    
    case "${1:-list}" in
        advance)
            local current=$(state_read "brick.yaml" "brick_gate" 2>/dev/null | tr -d ' ')
            local brick=$(state_read "brick.yaml" "active_brick" 2>/dev/null | tr -d ' ')
            local next=$((current + 1))
            
            state_write "brick.yaml" "brick_gate" "$next" 2>/dev/null
            echo ""
            echo "═══════════════════════════════════════"
            echo "  BRICK ADVANCED"
            echo "═══════════════════════════════════════"
            echo "  Brick: $brick"
            echo "  Gate:  $current → $next"
            echo "═══════════════════════════════════════"
            ;;
        status)
            local brick=$(state_read "brick.yaml" "active_brick" 2>/dev/null | tr -d ' ')
            local gate=$(state_read "brick.yaml" "brick_gate" 2>/dev/null | tr -d ' ')
            local status=$(state_read "brick.yaml" "brick_status" 2>/dev/null | tr -d ' ')
            echo "Brick: $brick | Gate: $gate | Status: $status"
            ;;
        list)
            echo "Active: $(state_read brick.yaml active_brick 2>/dev/null) (Gate $(state_read brick.yaml brick_gate 2>/dev/null))"
            ;;
        *) echo "Usage: ai brick [advance|status|list]" ;;
    esac
}
main() { brick_main "$@"; }
