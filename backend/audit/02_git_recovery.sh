#!/data/data/com.termux/files/usr/bin/bash
source "$(dirname "$0")/framework.sh"

BACKEND_DIR="$1"
REPORT_DIR="$2"
TEMP_DIR="$3"
cd "$BACKEND_DIR"

REPORT="$REPORT_DIR/02_git_recovery.md"

{
    echo "# Git Recovery Analysis"
    echo ""
    
    echo "## Current State"
    echo '```'
    echo "Branch: $(git branch --show-current 2>&1)"
    echo "HEAD: $(git rev-parse --short HEAD 2>&1)"
    echo "Status:"
    git status --short 2>&1
    echo '```'
    echo ""
    
    echo "## All Branches"
    echo '```'
    git branch -a 2>&1
    echo '```'
    echo ""
    
    echo "## Tags"
    echo '```'
    git tag 2>&1
    echo '```'
    echo ""
    
    echo "## Stash"
    echo '```'
    git stash list 2>&1
    echo '```'
    echo ""
    
    echo "## Reflog (Watchtower related)"
    echo '```'
    git reflog -100 2>&1 | grep -i "watchtower\|monitor\|metric\|health\|observability" || echo "No Watchtower entries"
    echo '```'
    echo ""
    
    echo "## Commit History (Watchtower)"
    echo '```'
    git log --all --oneline --grep="watchtower\|monitor\|metric\|health" -i -30 2>&1 || echo "No matches"
    echo '```'
    echo ""
    
    echo "## Deleted Watchtower Files"
    echo '```'
    git log --all --diff-filter=D --summary --oneline -100 2>&1 | grep -B1 -A1 -i "watchtower\|monitor\|metric" | head -50
    echo '```'
    echo ""
    
    echo "## Branches with Watchtower Code"
    echo '```'
    for branch in $(git branch -a 2>&1 | sed 's/^\*//' | sed 's/^[[:space:]]*//' | sed 's/remotes\///'); do
        if git ls-tree -r --name-only "$branch" 2>/dev/null | grep -qi "watchtower\|monitor"; then
            echo "BRANCH: $branch"
            git ls-tree -r --name-only "$branch" 2>/dev/null | grep -i "watchtower\|monitor" | head -10
            echo ""
        fi
    done
    echo '```'
    echo ""
    
    echo "## Unreachable Objects"
    echo '```'
    git fsck --lost-found 2>&1 | head -20
    echo '```'
    echo ""
    
    echo "## Recovery Candidates"
    echo '```'
    # Find commits that touched Watchtower files
    echo "Commits that added/modified Watchtower files:"
    git log --all --name-status --oneline -- "*Watchtower*" "*Monitor*" "*Metric*" "*Health*" 2>&1 | head -50
    echo '```'
    
} > "$REPORT" 2>&1

echo "Phase 2 complete"
