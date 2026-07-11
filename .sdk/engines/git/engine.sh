#!/data/data/com.termux/files/usr/bin/bash

if [ -n "$SDK_ROOT" ]; then
    KERNEL_DIR="$SDK_ROOT/kernel"
else
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    KERNEL_DIR="$(dirname "$(dirname "$SCRIPT_DIR")")/kernel"
fi

source "$KERNEL_DIR/common.sh" 2>/dev/null || true
source "$KERNEL_DIR/logger.sh" 2>/dev/null || true

# Verify git is available
git_require() {
    require_cmd "git" || {
        echo "Git is required but not installed"
        return 1
    }
}

# Get repository status summary
git_status() {
    local root
    root="$(get_project_root 2>/dev/null)" || return 1
    
    echo "GIT STATUS"
    echo "═══════════════════════════════════════"
    echo "  Branch:   $(git -C "$root" branch --show-current 2>/dev/null || echo 'unknown')"
    echo "  Remote:   $(git -C "$root" remote get-url origin 2>/dev/null || echo 'none')"
    echo "  Changes:  $(git -C "$root" status --porcelain 2>/dev/null | wc -l | tr -d ' ') files"
    echo "  Staged:   $(git -C "$root" diff --cached --name-only 2>/dev/null | wc -l | tr -d ' ') files"
    echo "  Commit:   $(git -C "$root" log -1 --format='%h - %s' 2>/dev/null || echo 'none')"
    echo "  Tag:      $(git -C "$root" describe --tags --abbrev=0 2>/dev/null || echo 'none')"
}

# Check if tree is clean
git_is_clean() {
    local root
    root="$(get_project_root 2>/dev/null)" || return 1
    [ -z "$(git -C "$root" status --porcelain 2>/dev/null)" ]
}

# Check if tree is dirty (has changes)
git_is_dirty() {
    ! git_is_clean
}

# Get current branch
git_branch() {
    local root
    root="$(get_project_root 2>/dev/null)" || return 1
    git -C "$root" branch --show-current 2>/dev/null
}

# Get last commit hash
git_last_commit() {
    local root
    root="$(get_project_root 2>/dev/null)" || return 1
    git -C "$root" log -1 --format=%H 2>/dev/null
}

# Get list of changed files
git_changed_files() {
    local root
    root="$(get_project_root 2>/dev/null)" || return 1
    git -C "$root" status --porcelain 2>/dev/null | awk '{print $2}'
}

# Get diff summary
git_diff_summary() {
    local root
    root="$(get_project_root 2>/dev/null)" || return 1
    git -C "$root" diff --stat 2>/dev/null
}

# Create a branch
git_branch_create() {
    local name="$1"
    local root
    root="$(get_project_root 2>/dev/null)" || return 1
    
    if git -C "$root" rev-parse --verify "$name" >/dev/null 2>&1; then
        echo "Branch already exists: $name"
        return 1
    fi
    
    git -C "$root" checkout -b "$name" 2>/dev/null && {
        log_info "Branch created: $name"
        echo "Branch created: $name"
    } || {
        echo "Failed to create branch: $name"
        return 1
    }
}

# Switch branch
git_branch_switch() {
    local name="$1"
    local root
    root="$(get_project_root 2>/dev/null)" || return 1
    
    if git_is_dirty; then
        echo "Uncommitted changes - stash or commit first"
        return 1
    fi
    
    git -C "$root" checkout "$name" 2>/dev/null && {
        echo "Switched to: $name"
    } || {
        echo "Failed to switch to: $name"
        return 1
    }
}

# Stage and commit
git_commit() {
    local message="$1"
    local root
    root="$(get_project_root 2>/dev/null)" || return 1
    
    if git_is_clean; then
        echo "Nothing to commit"
        return 0
    fi
    
    git -C "$root" add -A 2>/dev/null
    git -C "$root" commit -m "$message" 2>/dev/null && {
        local hash
        hash="$(git_last_commit)"
        log_info "Commit: $hash - $message"
        echo "Committed: $hash"
    } || {
        echo "Commit failed"
        return 1
    }
}

# Create a tag
git_tag() {
    local tag="$1"
    local message="${2:-Release $tag}"
    local root
    root="$(get_project_root 2>/dev/null)" || return 1
    
    git -C "$root" tag -a "$tag" -m "$message" 2>/dev/null && {
        log_info "Tag created: $tag"
        echo "Tag created: $tag"
    } || {
        echo "Tag already exists or failed: $tag"
        return 1
    }
}

# List tags
git_tags() {
    local root
    root="$(get_project_root 2>/dev/null)" || return 1
    git -C "$root" tag -l --sort=-v:refname 2>/dev/null | head -10
}

# Rollback helpers
git_rollback_file() {
    local file="$1"
    local root
    root="$(get_project_root 2>/dev/null)" || return 1
    git -C "$root" checkout -- "$file" 2>/dev/null && echo "Rolled back: $file"
}

git_rollback_last_commit() {
    local root
    root="$(get_project_root 2>/dev/null)" || return 1
    git -C "$root" reset --soft HEAD~1 2>/dev/null && echo "Last commit undone (changes preserved)"
}

# Show git log
git_log() {
    local limit="${1:-10}"
    local root
    root="$(get_project_root 2>/dev/null)" || return 1
    git -C "$root" log --oneline -"$limit" 2>/dev/null
}

# Validate branch name
git_validate_branch() {
    local name="$1"
    if echo "$name" | grep -qE '^[a-zA-Z0-9._/-]+$'; then
        return 0
    fi
    echo "Invalid branch name: $name"
    return 1
}

# Check if behind remote
git_behind_remote() {
    local root
    root="$(get_project_root 2>/dev/null)" || return 1
    local branch
    branch="$(git_branch)"
    
    git -C "$root" fetch origin 2>/dev/null
    local behind
    behind="$(git -C "$root" rev-list HEAD..origin/"$branch" --count 2>/dev/null)"
    echo "${behind:-0}"
}
