# Description: Git operations
# Requires: state

git_main() {
    local action="${1:-status}"
    
    local git_engine="$SDK_ROOT/engines/git/engine.sh"
    
    [ -f "$git_engine" ] || { echo "Git engine not found"; return 1; }
    source "$git_engine" 2>/dev/null || { echo "Failed to load git engine"; return 1; }
    
    case "$action" in
        status)    git_status ;;
        branch)    git_branch "${2:-}" ;;
        branches)  git branch -a 2>/dev/null ;;
        changes)   git_changed_files ;;
        diff)      git_diff_summary ;;
        log)       git_log "${2:-10}" ;;
        commit)    git_commit "${2:-auto: $(date)}" ;;
        tag)       git_tag "${2:-}" "${3:-}" ;;
        tags)      git_tags ;;
        clean)     git_is_clean && echo "Tree is clean" || echo "Tree has changes" ;;
        rollback)  git_rollback_file "${2:-}" ;;
        undo)      git_rollback_last_commit ;;
        behind)    echo "Behind remote by $(git_behind_remote) commits" ;;
        *)
            echo "Usage: ai git [status|branch|branches|changes|diff|log|commit|tag|tags|clean|rollback|undo|behind]"
            git_status
            ;;
    esac
}

main() { git_main "$@"; }
