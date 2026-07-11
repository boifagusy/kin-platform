# Description: Real-world examples
examples_main() {
    local help_engine="$SDK_ROOT/engines/help/engine.sh"
    [ -f "$help_engine" ] || { echo "Help engine not found"; return 1; }
    source "$help_engine" 2>/dev/null
    help_examples "${1:-}"
}
main() { examples_main "$@"; }
