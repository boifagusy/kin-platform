# Description: Mark current task complete
done_main() {
    source "$SDK_ROOT/kernel/state.sh" 2>/dev/null
    
    local task; task="$(grep "name:" .kin/state/task.yaml 2>/dev/null | sed 's/.*: //')"
    local brick; brick="$(state_read "brick.yaml" "active_brick" 2>/dev/null | tr -d ' ')"
    
    echo ""
    echo "═══════════════════════════════════════"
    echo "  TASK COMPLETE"
    echo "═══════════════════════════════════════"
    echo "  Task:  ${task:-unknown}"
    echo "  Brick: ${brick:-none}"
    echo ""
    echo "  ✅ Done. ai do \"next task\" to continue."
    echo "═══════════════════════════════════════"
    echo ""
    
    # Update task state
    sed -i 's/in_progress/complete/' .kin/state/task.yaml 2>/dev/null
}
main() { done_main "$@"; }
