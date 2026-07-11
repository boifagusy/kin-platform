#!/data/data/com.termux/files/usr/bin/bash

if [ -n "$SDK_ROOT" ]; then
    KERNEL_DIR="$SDK_ROOT/kernel"
    ENGINES_DIR="$SDK_ROOT/engines"
else
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    ENGINES_DIR="$(dirname "$SCRIPT_DIR")"
    KERNEL_DIR="$(dirname "$ENGINES_DIR")/kernel"
fi

source "$KERNEL_DIR/common.sh" 2>/dev/null || true
source "$KERNEL_DIR/logger.sh" 2>/dev/null || true
source "$KERNEL_DIR/state.sh" 2>/dev/null || true
source "$ENGINES_DIR/git/engine.sh" 2>/dev/null || true

# Check if GitHub CLI is available
github_require() {
    if command -v gh >/dev/null 2>&1; then
        return 0
    fi
    echo "GitHub CLI (gh) not installed"
    echo "Install: pkg install gh && gh auth login"
    return 1
}

# Check if repo has GitHub remote
github_has_remote() {
    local root
    root="$(get_project_root 2>/dev/null)" || return 1
    git -C "$root" remote get-url origin 2>/dev/null | grep -q "github.com"
}

# Get repository info
github_repo_info() {
    if ! github_has_remote; then
        echo "Not a GitHub repository"
        return 1
    fi
    
    local root
    root="$(get_project_root 2>/dev/null)" || return 1
    local remote
    remote="$(git -C "$root" remote get-url origin 2>/dev/null)"
    
    echo "GITHUB"
    echo "═══════════════════════════════════════"
    echo "  Remote:    $remote"
    echo "  Branch:    $(git_branch)"
    echo "  Behind:    $(git_behind_remote) commits"
    echo "  PRs:       $(github_pr_count 2>/dev/null || echo 'unknown')"
    echo "  Issues:    $(github_issue_count 2>/dev/null || echo 'unknown')"
}

# PR operations (work offline if gh not available)
github_pr_list() {
    if github_require 2>/dev/null; then
        gh pr list --limit 10 2>/dev/null || echo "No PRs or gh not configured"
    else
        echo "GitHub CLI not available - showing local branches"
        git branch -a 2>/dev/null | head -10
    fi
}

github_pr_create() {
    local title="$1"
    local body="${2:-}"
    local base="${3:-main}"
    
    if ! github_require 2>/dev/null; then
        echo "gh CLI required for PR creation"
        return 1
    fi
    
    if git_is_dirty; then
        echo "Commit changes first"
        return 1
    fi
    
    gh pr create --title "$title" --body "$body" --base "$base" 2>/dev/null && {
        log_info "PR created: $title"
    }
}

github_pr_status() {
    if github_require 2>/dev/null; then
        gh pr status 2>/dev/null || echo "No active PRs"
    else
        echo "GitHub CLI not available"
    fi
}

github_pr_count() {
    if github_require 2>/dev/null; then
        gh pr list --limit 100 2>/dev/null | wc -l | tr -d ' '
    else
        echo "0"
    fi
}

# Issue operations
github_issue_list() {
    if github_require 2>/dev/null; then
        gh issue list --limit 10 2>/dev/null || echo "No issues"
    else
        echo "GitHub CLI not available"
    fi
}

github_issue_count() {
    if github_require 2>/dev/null; then
        gh issue list --limit 100 2>/dev/null | wc -l | tr -d ' '
    else
        echo "0"
    fi
}

# Release operations
github_release_create() {
    local tag="$1"
    local title="$2"
    local notes="${3:-}"
    
    if ! github_require 2>/dev/null; then
        echo "gh CLI required for releases"
        return 1
    fi
    
    gh release create "$tag" --title "$title" --notes "$notes" 2>/dev/null && {
        log_info "Release created: $tag"
        echo "Release created: $tag"
    }
}

github_release_list() {
    if github_require 2>/dev/null; then
        gh release list --limit 10 2>/dev/null || echo "No releases"
    else
        echo "GitHub CLI not available"
    fi
}

# Sync state with GitHub
github_sync_state() {
    local root
    root="$(get_project_root 2>/dev/null)" || return 1
    
    state_write "github.yaml" "branch" "$(git_branch)"
    state_write "github.yaml" "status" "$(git -C "$root" status --porcelain 2>/dev/null | wc -l | tr -d ' ')"
    state_write "github.yaml" "last_commit" "$(git_last_commit)"
    
    if github_has_remote; then
        state_write "github.yaml" "pr_count" "$(github_pr_count 2>/dev/null || echo '0')"
    fi
}
