#!/data/data/com.termux/files/usr/bin/bash
# Lightweight pre-command check — only alerts on issues

os_quick_check() {
    local warnings=0
    
    # Only check if we're in a project
    git rev-parse --git-dir >/dev/null 2>&1 || return 0
    
    # Check contracts if PHP files changed
    local php_changes=$(git diff --name-only 2>/dev/null | grep "\.php$" | wc -l | tr -d ' ')
    local contracts=$(find .kin/contracts -name "*.json" 2>/dev/null | wc -l | tr -d ' ')
    
    if [ "$php_changes" -gt 3 ] && [ "$contracts" -eq 0 ]; then
        echo "💡 Tip: ai contract verify — certify your services"
        warnings=$((warnings + 1))
    fi
    
    # Check session
    if [ ! -f ".kin/state/session.yaml" ]; then
        echo "💡 Tip: ai session start — begin tracking your work"
        warnings=$((warnings + 1))
    fi
    
    return $warnings
}
