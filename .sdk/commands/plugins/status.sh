# Description: Show project status
status_main() {
    local project_root state_dir sdk_version
    project_root="$(get_project_root)" || return 1
    state_dir="$(get_state_dir)" || return 1
    sdk_version="$(read_yaml "$SDK_ROOT/sdk.yaml" "version")"

    echo "╔════════════════════════════════════════════╗"
    echo "║     KIN ENGINEERING SDK - STATUS           ║"
    echo "╚════════════════════════════════════════════╝"
    echo ""

    echo "PROJECT"
    echo "────────────────────────────────────────────"
    echo "  Path:       $project_root"
    echo "  Name:       $(basename "$project_root")"
    echo "  SDK:        v${sdk_version}"
    echo ""

    echo "SESSION"
    echo "────────────────────────────────────────────"
    if [ -f "$state_dir/session.yaml" ]; then
        echo "  ID:         $(read_yaml "$state_dir/session.yaml" "session_id")"
        echo "  Role:       $(read_yaml "$state_dir/session.yaml" "role")"
        echo "  Started:    $(read_yaml "$state_dir/session.yaml" "started")"
        echo "  Status:     $(read_yaml "$state_dir/session.yaml" "status")"
    else
        echo "  No active session"
    fi
    echo ""

    echo "AI STATE"
    echo "────────────────────────────────────────────"
    if [ -f "$state_dir/ai.yaml" ]; then
        echo "  Role:       $(read_yaml "$state_dir/ai.yaml" "active_role")"
        echo "  Stage:      $(read_yaml "$state_dir/ai.yaml" "current_stage")"
        echo "  Brick:      $(read_yaml "$state_dir/ai.yaml" "current_brick")"
        echo "  Waiting:    $(read_yaml "$state_dir/ai.yaml" "waiting_for")"
    else
        echo "  No AI state"
    fi
    echo ""

    echo "GIT"
    echo "────────────────────────────────────────────"
    echo "  Branch:     $(git branch --show-current 2>/dev/null || echo 'unknown')"
    echo "  Changes:    $(git status --porcelain 2>/dev/null | wc -l | tr -d ' ') files"
    echo "  Commit:     $(git log -1 --format=%h 2>/dev/null || echo 'none')"
    echo ""
}

main() {
    status_main "$@"
}
