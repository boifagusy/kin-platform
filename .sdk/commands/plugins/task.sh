# Description: Task state machine
task_main() {
    bash "$SDK_ROOT/engines/task/engine.sh" "${1:-status}"
}
main() { task_main "$@"; }
