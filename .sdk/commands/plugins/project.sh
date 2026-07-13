# Description: Project orchestrator
project_main() {
    bash "$SDK_ROOT/engines/project/engine.sh" "${1:-status}"
}
main() { project_main "$@"; }
