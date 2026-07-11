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
source "$KERNEL_DIR/errors.sh" 2>/dev/null || true
source "$KERNEL_DIR/logger.sh" 2>/dev/null || true
source "$KERNEL_DIR/state.sh" 2>/dev/null || true
source "$KERNEL_DIR/yaml.sh" 2>/dev/null || true
source "$KERNEL_DIR/filesystem.sh" 2>/dev/null || true
source "$ENGINES_DIR/brick/definitions.sh" 2>/dev/null || true

# Get bricks directory
get_bricks_dir() {
    local root
    root="$(get_project_root 2>/dev/null)" || root="$HOME"
    echo "$root/bricks"
}

# List all bricks
brick_list() {
    local bricks_dir
    bricks_dir="$(get_bricks_dir)"
    
    if [ ! -d "$bricks_dir" ]; then
        echo "  (no bricks directory)"
        return
    fi
    
    shopt -s nullglob
    local dirs=("$bricks_dir"/*/)
    shopt -u nullglob
    
    if [ ${#dirs[@]} -eq 0 ]; then
        echo "  (no bricks created)"
        return
    fi
    
    for dir in "${dirs[@]}"; do
        local name status
        name="$(basename "$dir")"
        if [ -f "$dir/brick.yaml" ]; then
            status="$(yaml_get_nested "$dir/brick.yaml" "brick" "status" 2>/dev/null || echo "unknown")"
            echo "  $name ($status)"
        else
            echo "  $name (no brick.yaml)"
        fi
    done
}

# Create a new brick
brick_create() {
    local name="$1"
    local bricks_dir
    bricks_dir="$(get_bricks_dir)"
    local brick_dir="$bricks_dir/$name"
    
    if [ -d "$brick_dir" ]; then
        echo "Brick already exists: $name"
        return 1
    fi
    
    echo "Creating brick: $name"
    
    # Create directory structure
    for dir in "${BRICK_DIRS[@]}"; do
        mkdir -p "$brick_dir/$dir"
        # Create .gitkeep in empty dirs
        touch "$brick_dir/$dir/.gitkeep" 2>/dev/null
    done
    
    # Create brick.yaml
    brick_template "$name" > "$brick_dir/brick.yaml"
    
    # Create README.md
    cat > "$brick_dir/README.md" <<MDEOF
# $name

## Description
TODO: Describe this brick

## Dependencies
None

## API
TODO: Document API endpoints

## Testing
TODO: Document testing approach
MDEOF
    
    # Create CHANGELOG.md
    cat > "$brick_dir/CHANGELOG.md" <<MDEOF
# Changelog

## [1.0.0] - $(date +%Y-%m-%d)
- Initial brick creation
MDEOF
    
    log_info "Brick created: $name"
    echo "Brick created: $brick_dir"
    brick_info "$name"
}

# Get brick info
brick_info() {
    local name="$1"
    local bricks_dir brick_dir
    bricks_dir="$(get_bricks_dir)"
    brick_dir="$bricks_dir/$name"
    
    if [ ! -d "$brick_dir" ]; then
        echo "Brick not found: $name"
        return 1
    fi
    
    echo ""
    echo "BRICK: $name"
    echo "═══════════════════════════════════════"
    
    if [ -f "$brick_dir/brick.yaml" ]; then
        echo "Version:     $(yaml_get_nested "$brick_dir/brick.yaml" "brick" "version")"
        echo "Status:      $(yaml_get_nested "$brick_dir/brick.yaml" "brick" "status")"
        echo "Gate:        $(yaml_get_nested "$brick_dir/brick.yaml" "brick" "gate")"
        echo "Assigned:    $(yaml_get_nested "$brick_dir/brick.yaml" "brick" "assigned_ai")"
        echo "Locked:      $(yaml_get_nested "$brick_dir/brick.yaml" "brick" "locked")"
        
        local deps
        deps="$(yaml_get_nested "$brick_dir/brick.yaml" "brick" "dependencies" 2>/dev/null)"
        [ -n "$deps" ] && echo "Deps:        $deps"
    fi
    
    echo "Directory:   $brick_dir"
    
    # Count files
    local file_count
    file_count="$(find "$brick_dir" -type f 2>/dev/null | wc -l | tr -d ' ')"
    echo "Files:       $file_count"
}

# Set brick status
brick_status() {
    local name="$1"
    local status="$2"
    local bricks_dir brick_dir
    bricks_dir="$(get_bricks_dir)"
    brick_dir="$bricks_dir/$name"
    
    if [ ! -f "$brick_dir/brick.yaml" ]; then
        echo "Brick not found: $name"
        return 1
    fi
    
    # Validate status
    case "$status" in
        planned|in_development|testing|complete|released) ;;
        *) echo "Invalid status: $status"; return 1 ;;
    esac
    
    sed -i "s/status:.*/status: $status/" "$brick_dir/brick.yaml"
    sed -i "s/last_updated:.*/last_updated: $(date -u +%Y-%m-%dT%H:%M:%SZ)/" "$brick_dir/brick.yaml"
    
    log_info "Brick $name status: $status"
    echo "Brick '$name' status set to: $status"
}

# Lock a brick for an AI
brick_lock() {
    local name="$1"
    local ai="${2:-unknown}"
    local bricks_dir brick_dir
    bricks_dir="$(get_bricks_dir)"
    brick_dir="$bricks_dir/$name"
    
    if [ ! -f "$brick_dir/brick.yaml" ]; then
        echo "Brick not found: $name"
        return 1
    fi
    
    local currently_locked
    currently_locked="$(yaml_get_nested "$brick_dir/brick.yaml" "brick" "locked" 2>/dev/null)"
    
    if [ "$currently_locked" = "true" ]; then
        local locked_by
        locked_by="$(yaml_get_nested "$brick_dir/brick.yaml" "brick" "locked_by" 2>/dev/null)"
        echo "Brick already locked by: $locked_by"
        return 1
    fi
    
    sed -i "s/locked:.*/locked: true/" "$brick_dir/brick.yaml"
    sed -i "s/locked_by:.*/locked_by: $ai/" "$brick_dir/brick.yaml"
    
    log_info "Brick $name locked by $ai"
    echo "Brick '$name' locked by $ai"
}

# Unlock a brick
brick_unlock() {
    local name="$1"
    local bricks_dir brick_dir
    bricks_dir="$(get_bricks_dir)"
    brick_dir="$bricks_dir/$name"
    
    if [ ! -f "$brick_dir/brick.yaml" ]; then
        echo "Brick not found: $name"
        return 1
    fi
    
    sed -i "s/locked:.*/locked: false/" "$brick_dir/brick.yaml"
    sed -i "s/locked_by:.*/locked_by: null/" "$brick_dir/brick.yaml"
    
    log_info "Brick $name unlocked"
    echo "Brick '$name' unlocked"
}

# Add dependency to a brick
brick_depend() {
    local name="$1"
    local dependency="$2"
    local bricks_dir brick_dir
    bricks_dir="$(get_bricks_dir)"
    brick_dir="$bricks_dir/$name"
    
    if [ ! -f "$brick_dir/brick.yaml" ]; then
        echo "Brick not found: $name"
        return 1
    fi
    
    # Check if dependency already listed
    if grep -q "  - $dependency" "$brick_dir/brick.yaml"; then
        echo "Dependency already exists: $dependency"
        return 0
    fi
    
    # Add after dependencies line
    sed -i "/dependencies:/a\\  - $dependency" "$brick_dir/brick.yaml"
    
    log_info "Brick $name now depends on $dependency"
    echo "Added dependency: $name -> $dependency"
}

# Validate brick integrity
brick_validate() {
    local name="$1"
    local bricks_dir brick_dir
    bricks_dir="$(get_bricks_dir)"
    brick_dir="$bricks_dir/$name"
    local errors=0
    
    if [ ! -d "$brick_dir" ]; then
        echo "Brick not found: $name"
        return 1
    fi
    
    echo "Validating brick: $name"
    
    # Check brick.yaml exists
    if [ ! -f "$brick_dir/brick.yaml" ]; then
        echo "  ❌ Missing brick.yaml"
        errors=$((errors + 1))
    else
        echo "  ✅ brick.yaml"
    fi
    
    # Check README exists
    if [ ! -f "$brick_dir/README.md" ]; then
        echo "  ❌ Missing README.md"
        errors=$((errors + 1))
    else
        echo "  ✅ README.md"
    fi
    
    # Check required directories
    for dir in backend tests docs; do
        if [ -d "$brick_dir/$dir" ]; then
            echo "  ✅ $dir/"
        else
            echo "  ❌ Missing $dir/"
            errors=$((errors + 1))
        fi
    done
    
    if [ $errors -eq 0 ]; then
        echo "  Brick is valid"
        return 0
    else
        echo "  $errors issue(s) found"
        return 1
    fi
}
