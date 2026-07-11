# Description: GitHub operations
# Requires: state git

github_main() {
    local action="${1:-info}"
    
    local github_engine="$SDK_ROOT/engines/github/engine.sh"
    
    [ -f "$github_engine" ] || { echo "GitHub engine not found"; return 1; }
    source "$github_engine" 2>/dev/null || { echo "Failed to load GitHub engine"; return 1; }
    
    case "$action" in
        info)    github_repo_info ;;
        prs)     github_pr_list ;;
        pr)      github_pr_status ;;
        create-pr) github_pr_create "${2:-}" "${3:-}" "${4:-main}" ;;
        issues)  github_issue_list ;;
        releases) github_release_list ;;
        release) github_release_create "${2:-}" "${3:-}" "${4:-}" ;;
        sync)    github_sync_state && echo "State synced" ;;
        *)
            echo "Usage: ai github [info|prs|pr|create-pr|issues|releases|release|sync]"
            github_repo_info 2>/dev/null || echo "Run 'ai github info' for details"
            ;;
    esac
}

main() { github_main "$@"; }
