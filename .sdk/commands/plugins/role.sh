role_main() {
    source "$SDK_ROOT/engines/role/engine.sh" 2>/dev/null
    case "${1:-status}" in
        set)    role_set "${2:-architect}" ;;
        status) role_status ;;
        auto)   role_auto ;;
        detect) role_detect ;;
        cmds)   role_commands "${2:-}" ;;
        *)      echo "ai role [set|status|auto|detect|cmds]" ;;
    esac
}
main() { role_main "$@"; }
