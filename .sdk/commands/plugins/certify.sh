# Description: Task Certification Engine
certify_main() {
    bash "$SDK_ROOT/engines/certify/engine.sh" "${1:-list}"
}
main() { certify_main "$@"; }
