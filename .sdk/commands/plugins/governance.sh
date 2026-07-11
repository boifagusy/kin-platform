# Description: Governance Engine — automatic enforcement
governance_main() {
    source "$SDK_ROOT/engines/governance/engine.sh" 2>/dev/null
    governance_check "${1:-unknown}" "${2:-}"
}
main() { governance_main "$@"; }
