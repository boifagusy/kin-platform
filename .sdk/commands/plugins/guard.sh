# Description: Gate Guard — pre-implementation enforcement
guard_main() {
    source "$SDK_ROOT/engines/gate_guard/engine.sh" 2>/dev/null
    gate_guard_check "${1:-implementation}"
}
main() { guard_main "$@"; }
