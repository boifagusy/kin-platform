# Description: Project Discovery — build registry, investigate, confidence
discovery_main() {
    bash "$SDK_ROOT/engines/intelligence/discovery/engine.sh" "${1:-build}"
}
main() { discovery_main "$@"; }
