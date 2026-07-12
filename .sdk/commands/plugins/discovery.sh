# Description: Project Discovery — build registry, investigate, confidence
discovery_main() {
    local action="${1:-build}"
    bash "$SDK_ROOT/engines/intelligence/discovery/engine.sh" "$action" "${2:-}"
}
main() { discovery_main "$@"; }
