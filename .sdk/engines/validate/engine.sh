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
source "$KERNEL_DIR/state.sh" 2>/dev/null || true
source "$KERNEL_DIR/yaml.sh" 2>/dev/null || true
source "$KERNEL_DIR/filesystem.sh" 2>/dev/null || true
source "$ENGINES_DIR/validate/framework.sh" 2>/dev/null || true

# ==========================================
# KERNEL VALIDATION
# ==========================================
validate_kernel() {
    validation_init "kernel" "kernel"
    
    # Check kernel files exist
    local kernel_dir
    if [ -n "$SDK_ROOT" ]; then kernel_dir="$SDK_ROOT/kernel"
    else kernel_dir=".sdk/kernel"; fi
    
    for f in common.sh errors.sh logger.sh filesystem.sh yaml.sh state.sh project.sh adapter.sh plugin.sh; do
        if [ -f "$kernel_dir/$f" ]; then
            validation_check "kernel_file_$f" "pass" "$f exists"
        else
            validation_check "kernel_file_$f" "fail" "$f missing" "Create $kernel_dir/$f"
        fi
    done
    
    # Check kernel syntax
    for f in "$kernel_dir"/*.sh; do
        [ -f "$f" ] || continue
        local name="$(basename "$f")"
        if bash -n "$f" 2>/dev/null; then
            validation_check "syntax_$name" "pass" ""
        else
            validation_check "syntax_$name" "fail" "Syntax error" "Run: bash -n $f"
        fi
    done
    
    # Check state integrity
    if state_validate 2>/dev/null; then
        validation_check "state_integrity" "pass" "State files valid"
    else
        validation_check "state_integrity" "fail" "State corruption" "Run: state_repair"
    fi
    
    # Check plugins
    local plugin_count
    plugin_count=$(find .sdk/commands/plugins -name "*.sh" 2>/dev/null | wc -l | tr -d ' ')
    if [ "${plugin_count:-0}" -gt 0 ]; then
        validation_check "plugins_found" "pass" "$plugin_count plugins"
    else
        validation_check "plugins_found" "warn" "No plugins found" "Install plugins"
    fi
    
    validation_report
}

# ==========================================
# ENGINE VALIDATION
# ==========================================
validate_engine() {
    local engine="$1"
    validation_init "$engine" "engine"
    
    local engine_dir
    if [ -n "$SDK_ROOT" ]; then engine_dir="$SDK_ROOT/engines/$engine"
    else engine_dir=".sdk/engines/$engine"; fi
    
    # Check engine directory
    if [ -d "$engine_dir" ]; then
        validation_check "engine_dir" "pass" "Directory exists"
    else
        validation_check "engine_dir" "fail" "Engine not found" "Create $engine_dir"
        validation_report
        return 1
    fi
    
    # Check engine script
    if [ -f "$engine_dir/engine.sh" ]; then
        validation_check "engine_script" "pass" "engine.sh exists"
        
        if bash -n "$engine_dir/engine.sh" 2>/dev/null; then
            validation_check "engine_syntax" "pass" "Syntax valid"
        else
            validation_check "engine_syntax" "fail" "Syntax error"
        fi
    else
        validation_check "engine_script" "fail" "engine.sh missing"
    fi
    
    # Check plugin exists
    local plugin_name="$engine"
    [ "$engine" = "knowledge" ] && plugin_name="knowledge"
    
    if [ -f ".sdk/commands/plugins/${plugin_name}.sh" ]; then
        validation_check "plugin_exists" "pass" "Plugin installed"
    else
        validation_check "plugin_exists" "warn" "No CLI plugin" "Create .sdk/commands/plugins/${plugin_name}.sh"
    fi
    
    validation_report
}

# ==========================================
# GATE VALIDATION
# ==========================================
validate_gate() {
    local gate="$1"
    validation_init "gate_$gate" "gate"
    
    source "$ENGINES_DIR/gate/engine.sh" 2>/dev/null || source ".sdk/engines/gate/engine.sh" 2>/dev/null
    
    # Check gate is valid
    if [ "$gate" -ge 0 ] 2>/dev/null && [ "$gate" -le 11 ] 2>/dev/null; then
        validation_check "gate_valid" "pass" "Gate $gate is valid"
    else
        validation_check "gate_valid" "fail" "Invalid gate number" "Use gates 0-11"
        validation_report
        return 1
    fi
    
    # Check entry requirements
    local entry
    entry="$(gate_entry_requirements "$gate" 2>/dev/null)"
    if [ -n "$entry" ]; then
        validation_check "entry_requirements" "pass" "Entry requirements defined"
    else
        validation_check "entry_requirements" "fail" "No entry requirements"
    fi
    
    # Check exit requirements
    local exit_req
    exit_req="$(gate_exit_requirements "$gate" 2>/dev/null)"
    if [ -n "$exit_req" ]; then
        validation_check "exit_requirements" "pass" "Exit requirements defined"
    else
        validation_check "exit_requirements" "fail" "No exit requirements"
    fi
    
    validation_report
}

# ==========================================
# BRICK VALIDATION
# ==========================================
validate_brick() {
    local name="$1"
    validation_init "$name" "brick"
    
    local brick_dir="bricks/$name"
    
    # Check brick directory
    if [ -d "$brick_dir" ]; then
        validation_check "brick_dir" "pass" "Directory exists"
    else
        validation_check "brick_dir" "fail" "Brick not found" "Run: ai brick create $name"
        validation_report
        return 1
    fi
    
    # Check brick.yaml
    if [ -f "$brick_dir/brick.yaml" ]; then
        validation_check "brick_yaml" "pass" "brick.yaml exists"
    else
        validation_check "brick_yaml" "fail" "brick.yaml missing" "Run: ai brick create $name"
    fi
    
    # Check README
    if [ -f "$brick_dir/README.md" ]; then
        validation_check "readme" "pass" "README exists"
    else
        validation_check "readme" "fail" "README missing"
    fi
    
    # Check key directories
    for dir in backend tests docs; do
        if [ -d "$brick_dir/$dir" ]; then
            validation_check "dir_$dir" "pass" "$dir/ exists"
        else
            validation_check "dir_$dir" "fail" "$dir/ missing" "mkdir -p $brick_dir/$dir"
        fi
    done
    
    # Check if locked
    local locked
    locked="$(yaml_get_nested "$brick_dir/brick.yaml" "brick" "locked" 2>/dev/null)"
    if [ "$locked" = "true" ]; then
        local locked_by
        locked_by="$(yaml_get_nested "$brick_dir/brick.yaml" "brick" "locked_by" 2>/dev/null)"
        validation_check "brick_lock" "warn" "Locked by $locked_by"
    else
        validation_check "brick_lock" "pass" "Unlocked"
    fi
    
    validation_report
}

# ==========================================
# PROJECT VALIDATION
# ==========================================
validate_project() {
    local root
    root="$(get_project_root 2>/dev/null)" || root="."
    validation_init "$(basename "$root")" "project"
    
    # Git repository
    if git rev-parse --git-dir >/dev/null 2>&1; then
        validation_check "git_repo" "pass" "Git repository"
    else
        validation_check "git_repo" "fail" "Not a Git repository"
    fi
    
    # SDK installed
    if [ -f ".sdk/sdk.yaml" ]; then
        local version
        version="$(yaml_get ".sdk/sdk.yaml" "version" 2>/dev/null)"
        validation_check "sdk_installed" "pass" "SDK v${version}"
    else
        validation_check "sdk_installed" "fail" "SDK not installed" "Run: ai install"
    fi
    
    # Governance docs
    local gov_dir="docs/governance"
    if [ -d "$gov_dir" ]; then
        local gov_count
        gov_count=$(find "$gov_dir" -type f 2>/dev/null | wc -l | tr -d ' ')
        validation_check "governance" "pass" "$gov_count governance files"
    else
        validation_check "governance" "fail" "No governance directory"
    fi
    
    # State directory
    if [ -d ".kin/state" ]; then
        validation_check "state_dir" "pass" "State directory exists"
    else
        validation_check "state_dir" "fail" "No state directory" "Run: ai session start"
    fi
    
    # Termux
    if is_termux; then
        validation_check "termux" "pass" "Termux detected"
    else
        validation_check "termux" "warn" "Not in Termux"
    fi
    
    validation_report
}

# ==========================================
# RELEASE VALIDATION
# ==========================================
validate_release() {
    local version="${1:-}"
    [ -z "$version" ] && version="$(git describe --tags --abbrev=0 2>/dev/null || echo 'unknown')"
    validation_init "$version" "release"
    
    source "$ENGINES_DIR/release/engine.sh" 2>/dev/null || source ".sdk/engines/release/engine.sh" 2>/dev/null
    
    # Version format
    if release_validate_version "$version" 2>/dev/null; then
        validation_check "version_format" "pass" "Valid semver"
    else
        validation_check "version_format" "fail" "Invalid version format" "Use X.Y.Z"
    fi
    
    # Changelog
    if [ -f "CHANGELOG.md" ]; then
        validation_check "changelog" "pass" "CHANGELOG.md exists"
    else
        validation_check "changelog" "fail" "CHANGELOG.md missing" "Run: ai release changelog"
    fi
    
    # Tag exists
    if git rev-parse "v$version" >/dev/null 2>&1; then
        validation_check "tag_exists" "pass" "Tag v$version exists"
    else
        validation_check "tag_exists" "warn" "Tag v$version not found"
    fi
    
    # Tree clean
    if git_is_clean 2>/dev/null; then
        validation_check "tree_clean" "pass" "Working tree clean"
    else
        validation_check "tree_clean" "fail" "Uncommitted changes" "Commit or stash changes"
    fi
    
    validation_report
}

# ==========================================
# FULL VALIDATION
# ==========================================
validate_all() {
    echo ""
    echo "════════════════════════════════════════════"
    echo "  ENGINEERING OS — FULL VALIDATION"
    echo "════════════════════════════════════════════"
    
    validate_kernel
    echo ""
    
    local engines=("workflow" "event" "audit" "knowledge" "git" "github" "release")
    for engine in "${engines[@]}"; do
        validate_engine "$engine" 2>/dev/null
        echo ""
    done
    
    validate_project
    echo ""
    
    echo "Full validation complete."
}
