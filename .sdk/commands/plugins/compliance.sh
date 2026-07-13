# Description: Constitution Compliance — prove the OS follows its own rules
compliance_main() {
    bash "$SDK_ROOT/engines/compliance/engine.sh"
}
main() { compliance_main "$@"; }
