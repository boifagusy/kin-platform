# Description: Guided tutorial
tutorial_main() {
    local help_engine="$SDK_ROOT/engines/help/engine.sh"
    [ -f "$help_engine" ] || { echo "Help engine not found"; return 1; }
    source "$help_engine" 2>/dev/null
    help_tutorial "${1:-1}"
}
main() { tutorial_main "$@"; }
