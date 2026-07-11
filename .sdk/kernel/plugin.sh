#!/data/data/com.termux/files/usr/bin/bash

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/errors.sh" 2>/dev/null || true
source "$SCRIPT_DIR/filesystem.sh" 2>/dev/null || true
source "$SCRIPT_DIR/logger.sh" 2>/dev/null || true

# Get plugins directory
get_plugin_dir() {
    local sdk_root
    sdk_root="$(dirname "$SCRIPT_DIR")"
    echo "$sdk_root/commands/plugins"
}

# List all plugins
plugin_list() {
    local plugin_dir
    plugin_dir="$(get_plugin_dir)"
    if [ ! -d "$plugin_dir" ]; then
        echo "  (no plugins directory)"
        return
    fi
    
    shopt -s nullglob
    local plugins=("$plugin_dir"/*.sh)
    shopt -u nullglob
    
    if [ ${#plugins[@]} -eq 0 ]; then
        echo "  (no plugins installed)"
    else
        for plugin in "${plugins[@]}"; do
            local name
            name="$(basename "$plugin" .sh)"
            local desc=""
            # Extract description from plugin file
            desc="$(grep "^# Description:" "$plugin" 2>/dev/null | sed 's/.*: *//')"
            if [ -n "$desc" ]; then
                echo "  $name - $desc"
            else
                echo "  $name"
            fi
        done
    fi
    return 0
}

# Check if plugin exists
plugin_exists() {
    local name="$1"
    local plugin_dir
    plugin_dir="$(get_plugin_dir)"
    [ -f "$plugin_dir/${name}.sh" ]
}

# Load a single plugin in isolated subshell to detect errors
plugin_load() {
    local name="$1"
    local plugin_dir
    plugin_dir="$(get_plugin_dir)"
    
    if [ ! -f "$plugin_dir/${name}.sh" ]; then
        log_error "Plugin not found: $name"
        return $E_PLUGIN_FAILED
    fi
    
    # Test syntax first
    if ! bash -n "$plugin_dir/${name}.sh" 2>/dev/null; then
        log_error "Plugin syntax error: $name"
        return $E_PLUGIN_FAILED
    fi
    
    # Source the plugin
    source "$plugin_dir/${name}.sh" 2>/dev/null || {
        log_error "Plugin load failed: $name"
        return $E_PLUGIN_FAILED
    }
    
    log_debug "Plugin loaded: $name"
    return 0
}

# Load plugin with dependency resolution
plugin_require() {
    local name="$1"
    local plugin_dir
    plugin_dir="$(get_plugin_dir)"
    
    # Parse dependencies from plugin file header
    local deps
    deps="$(grep "^# Requires:" "$plugin_dir/${name}.sh" 2>/dev/null | sed 's/.*: *//')"
    
    # Load dependencies first
    if [ -n "$deps" ]; then
        for dep in $deps; do
            if ! plugin_exists "$dep"; then
                log_error "Missing dependency: $dep (required by $name)"
                return $E_PLUGIN_FAILED
            fi
            plugin_load "$dep" || return $E_PLUGIN_FAILED
        done
    fi
    
    # Load the plugin itself
    plugin_load "$name"
}

# Execute a plugin function with error isolation
plugin_execute() {
    local name="$1"
    local func="${2:-main}"
    
    # Load plugin
    plugin_require "$name" || return $E_PLUGIN_FAILED
    
    # Check if function exists
    if ! type "$func" >/dev/null 2>&1; then
        log_error "Function not found: $func (in $name)"
        return $E_PLUGIN_FAILED
    fi
    
    # Execute in current shell (plugins need access to state)
    "$func" 2>/tmp/plugin_error_$$.log || {
        local exit_code=$?
        log_error "Plugin execution failed: $name::$func (exit: $exit_code)"
        cat /tmp/plugin_error_$$.log >&2 2>/dev/null
        rm -f /tmp/plugin_error_$$.log
        return $exit_code
    }
    
    rm -f /tmp/plugin_error_$$.log
    return 0
}

# Hot-reload a plugin (re-source without restart)
plugin_reload() {
    local name="$1"
    log_info "Reloading plugin: $name"
    plugin_load "$name"
}

# Validate all plugins
plugin_validate_all() {
    local plugin_dir
    plugin_dir="$(get_plugin_dir)"
    local errors=0
    
    if [ ! -d "$plugin_dir" ]; then
        echo "Plugin directory not found"
        return 1
    fi
    
    shopt -s nullglob
    local plugins=("$plugin_dir"/*.sh)
    shopt -u nullglob
    
    for plugin in "${plugins[@]}"; do
        local name
        name="$(basename "$plugin" .sh)"
        
        # Syntax check
        if ! bash -n "$plugin" 2>/dev/null; then
            echo "  FAIL (syntax): $name"
            errors=$((errors + 1))
            continue
        fi
        
        # Has main function check
        if ! grep -q "^main()" "$plugin" 2>/dev/null && ! grep -q "^${name}_main()" "$plugin" 2>/dev/null; then
            echo "  WARN (no main): $name"
        else
            echo "  OK: $name"
        fi
    done
    
    if [ $errors -gt 0 ]; then
        log_error "$errors plugin(s) failed validation"
        return 1
    fi
    
    return 0
}
