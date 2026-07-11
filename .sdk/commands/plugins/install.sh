# Description: Install SDK into a project
install_main() {
    local target="$1"
    local source_sdk="$SDK_ROOT"

    echo "KIN ENGINEERING SDK - INSTALLER"
    echo "═══════════════════════════════════════"

    if [ ! -f "$source_sdk/sdk.yaml" ]; then
        echo "ERROR: SDK source not found"
        return 1
    fi

    echo "Source: $source_sdk"

    if [ -z "$target" ] || [ "$target" = "." ]; then
        target="$(pwd)"
    fi

    target="$(cd "$target" 2>/dev/null && pwd)" || {
        echo "Cannot access: $target"
        return 1
    }

    if ! git -C "$target" rev-parse --git-dir > /dev/null 2>&1; then
        echo "Not a Git repository. Run: git init"
        return 1
    fi

    echo "Target: $target"
    echo ""

    mkdir -p "$target/.sdk/core/lib" "$target/.sdk/core/state" "$target/.sdk/commands/plugins" "$target/.sdk/templates"
    mkdir -p "$target/.kin/state" "$target/.kin/cache" "$target/.kin/history"

    cp "$source_sdk/sdk.yaml" "$target/.sdk/"
    cp "$source_sdk/core/lib/"*.sh "$target/.sdk/core/lib/"
    cp "$source_sdk/commands/ai" "$target/.sdk/commands/"
    cp "$source_sdk/commands/plugins/"*.sh "$target/.sdk/commands/plugins/"

    chmod +x "$target/.sdk/commands/ai" "$target/.sdk/commands/plugins/"*.sh "$target/.sdk/core/lib/"*.sh

    echo "Done."
    echo "Run: cd $target && ./.sdk/commands/ai session start"
}

main() { install_main "$@"; }
