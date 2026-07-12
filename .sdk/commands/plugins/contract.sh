# Description: Contract verification engine
contract_main() {
    bash "$SDK_ROOT/engines/contracts/engine.sh" "${1:-status}"
}
main() { contract_main "$@"; }
