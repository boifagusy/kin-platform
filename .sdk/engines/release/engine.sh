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
source "$KERNEL_DIR/yaml.sh" 2>/dev/null || true
source "$ENGINES_DIR/git/engine.sh" 2>/dev/null || true
source "$ENGINES_DIR/event/engine.sh" 2>/dev/null || true

# Validate semver
release_validate_version() {
    local version="$1"
    if echo "$version" | grep -qE '^[0-9]+\.[0-9]+\.[0-9]+(-rc[0-9]+)?$'; then
        return 0
    fi
    echo "Invalid version: $version (use X.Y.Z or X.Y.Z-rcN)"
    return 1
}

# Get current version from git tags
release_current_version() {
    git describe --tags --abbrev=0 2>/dev/null || echo "0.0.0"
}

# Suggest next version
release_suggest_version() {
    local current bump="${1:-patch}"
    current="$(release_current_version)"
    
    local major minor patch
    major="${current%%.*}"; current="${current#*.}"
    minor="${current%%.*}"; patch="${current#*.}"
    patch="${patch%%-*}"
    
    case "$bump" in
        major) echo "$((major + 1)).0.0" ;;
        minor) echo "$major.$((minor + 1)).0" ;;
        patch) echo "$major.$minor.$((patch + 1))" ;;
        rc)    echo "$major.$minor.$patch-rc1" ;;
        *)     echo "$major.$minor.$patch" ;;
    esac
}

# Generate changelog from git log
release_changelog() {
    local since_tag="${1:-}"
    local root
    root="$(get_project_root 2>/dev/null)" || return 1
    
    echo "# Changelog"
    echo ""
    
    if [ -n "$since_tag" ]; then
        echo "## Changes since $since_tag"
        echo ""
        git -C "$root" log "${since_tag}..HEAD" --oneline 2>/dev/null | while read line; do
            echo "- $line"
        done
    else
        echo "## All changes"
        echo ""
        git -C "$root" log --oneline 2>/dev/null | head -50 | while read line; do
            echo "- $line"
        done
    fi
}

# Release checklist
release_checklist() {
    local version="${1:-unknown}"
    
    echo "RELEASE CHECKLIST: $version"
    echo "═══════════════════════════════════════"
    echo ""
    
    local checks=0
    local passed=0
    
    # Check 1: Tree clean
    echo -n "  [ ] Tree is clean... "
    if git_is_clean 2>/dev/null; then
        echo "✅"
        passed=$((passed + 1))
    else
        echo "❌ (uncommitted changes)"
    fi
    checks=$((checks + 1))
    
    # Check 2: All tests passing (placeholder)
    echo -n "  [ ] Tests passing... "
    echo "⚠️  (verify manually)"
    checks=$((checks + 1))
    
    # Check 3: Documentation updated
    echo -n "  [ ] CHANGELOG.md... "
    if [ -f "CHANGELOG.md" ]; then
        echo "✅"
        passed=$((passed + 1))
    else
        echo "❌ (create CHANGELOG.md)"
    fi
    checks=$((checks + 1))
    
    # Check 4: Gate check
    local gate
    gate="$(state_read "gate.yaml" "current" 2>/dev/null | tr -d ' ')"
    echo -n "  [ ] Gate 11 (Release)... "
    if [ "${gate:-0}" -ge 11 ]; then
        echo "✅"
        passed=$((passed + 1))
    else
        echo "❌ (currently at Gate $gate)"
    fi
    checks=$((checks + 1))
    
    # Check 5: No known bugs high severity
    echo -n "  [ ] No critical bugs... "
    echo "⚠️  (verify manually)"
    checks=$((checks + 1))
    
    echo ""
    echo "  $passed/$checks checks passed"
    
    if [ $passed -eq $checks ]; then
        echo "  READY FOR RELEASE"
        return 0
    else
        echo "  NOT READY - $((checks - passed)) issues remain"
        return 1
    fi
}

# Create release package
release_create() {
    local version="$1"
    local type="${2:-patch}"
    
    if [ -z "$version" ]; then
        version="$(release_suggest_version "$type")"
    fi
    
    if ! release_validate_version "$version"; then
        return 1
    fi
    
    echo "Preparing release: $version"
    echo "═══════════════════════════════════════"
    echo ""
    
    # Run checklist
    release_checklist "$version" || {
        echo ""
        echo "Checklist not complete. Fix issues before release."
        return 1
    }
    
    # Generate changelog
    local prev_version
    prev_version="$(release_current_version)"
    local changelog_file="CHANGELOG.md"
    
    echo "Generating changelog..."
    release_changelog "$prev_version" > "$changelog_file"
    echo "  Changelog: $changelog_file"
    
    # Create release branch
    local branch="release/$version"
    if git_branch_create "$branch" 2>/dev/null; then
        echo "  Branch: $branch"
    fi
    
    # Create tag
    git_tag "v$version" "Release v$version" 2>/dev/null
    echo "  Tag: v$version"
    
    # Update state
    state_write "release.yaml" "version" "$version"
    state_write "release.yaml" "status" "prepared"
    state_write "release.yaml" "tag" "v$version"
    state_write "release.yaml" "branch" "$branch"
    state_write "release.yaml" "changelog" "$changelog_file"
    
    # Emit event
    event_publish "gate.passed" "release_engine" "version: $version" 2>/dev/null || true
    
    echo ""
    echo "Release $version prepared"
    echo ""
    echo "Next steps:"
    echo "  1. Review changelog: cat $changelog_file"
    echo "  2. Push: git push origin $branch && git push origin v$version"
    echo "  3. Create GitHub release: ai github release v$version"
}

# Verify release artifacts
release_verify() {
    local version="$1"
    local errors=0
    
    echo "Verifying release: $version"
    echo "═══════════════════════════════════════"
    
    # Check tag exists
    if git rev-parse "v$version" >/dev/null 2>&1; then
        echo "  ✅ Tag v$version exists"
    else
        echo "  ❌ Tag v$version not found"
        errors=$((errors + 1))
    fi
    
    # Check changelog
    if [ -f "CHANGELOG.md" ]; then
        echo "  ✅ CHANGELOG.md exists"
    else
        echo "  ❌ CHANGELOG.md missing"
        errors=$((errors + 1))
    fi
    
    # Check release notes
    if [ -f "RELEASE_NOTES.md" ]; then
        echo "  ✅ RELEASE_NOTES.md exists"
    else
        echo "  ⚠️  RELEASE_NOTES.md missing (optional)"
    fi
    
    if [ $errors -eq 0 ]; then
        echo "  Release verified"
        return 0
    else
        echo "  $errors issue(s) found"
        return 1
    fi
}

# Rollback release
release_rollback() {
    local version="$1"
    
    echo "Rolling back release: $version"
    echo "═══════════════════════════════════════"
    
    # Delete tag
    if git rev-parse "v$version" >/dev/null 2>&1; then
        git tag -d "v$version" 2>/dev/null && echo "  Tag v$version deleted"
    fi
    
    # Delete release branch
    local branch="release/$version"
    if git rev-parse --verify "$branch" >/dev/null 2>&1; then
        git branch -D "$branch" 2>/dev/null && echo "  Branch $branch deleted"
    fi
    
    state_write "release.yaml" "status" "rolled_back"
    echo "  Release rolled back"
}
