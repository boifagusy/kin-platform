# Description: Alias for copy
clip_main() { source "$SDK_ROOT/commands/plugins/copy.sh" && copy_main "$@"; }
main() { clip_main "$@"; }
