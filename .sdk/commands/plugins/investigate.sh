# Description: Investigation Engine — no implementation without investigation
investigate_main() {
    bash "$SDK_ROOT/engines/investigate/engine.sh" "${1:-list}"
}
main() { investigate_main "$@"; }
