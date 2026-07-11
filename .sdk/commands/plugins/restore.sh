# Description: System restoration engine
restore_main() {
    bash "$SDK_ROOT/engines/restore/engine.sh" "$@"
}
main() { restore_main "$@"; }
