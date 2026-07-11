# Description: Validation engine
# Requires: state

validate_main() {
    local action="${1:-all}"
    
    local validate_engine="$SDK_ROOT/engines/validate/engine.sh"
    [ -f "$validate_engine" ] || { echo "Validation engine not found"; return 1; }
    source "$validate_engine" 2>/dev/null || { echo "Failed to load"; return 1; }
    
    case "$action" in
        all)       validate_all ;;
        kernel)    validate_kernel ;;
        engine)    validate_engine "${2:-}" ;;
        gate)      validate_gate "${2:-0}" ;;
        brick)     validate_brick "${2:-}" ;;
        project)   validate_project ;;
        release)   validate_release "${2:-}" ;;
        *)
            echo "Usage: ai validate [all|kernel|engine|gate|brick|project|release]"
            echo ""
            validate_project 2>/dev/null
            ;;
    esac
}

main() { validate_main "$@"; }
