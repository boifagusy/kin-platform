#!/data/data/com.termux/files/usr/bin/bash

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/errors.sh" 2>/dev/null || true
source "$SCRIPT_DIR/project.sh" 2>/dev/null || true

# Get adapter directory
get_adapter_dir() {
    local sdk_root
    sdk_root="$(dirname "$SCRIPT_DIR")"
    echo "$sdk_root/adapters"
}

# List available adapters
adapter_list() {
    local adapter_dir
    adapter_dir="$(get_adapter_dir)"
    if [ -d "$adapter_dir" ]; then
        for dir in "$adapter_dir"/*/; do
            [ -d "$dir" ] && echo "  $(basename "$dir")"
        done
    fi
}

# Check if adapter exists
adapter_exists() {
    local name="$1"
    local adapter_dir
    adapter_dir="$(get_adapter_dir)"
    [ -f "$adapter_dir/$name/paths.sh" ]
}

# Load an adapter
adapter_load() {
    local name="$1"
    local adapter_dir
    adapter_dir="$(get_adapter_dir)"
    
    if [ ! -f "$adapter_dir/$name/paths.sh" ]; then
        throw $E_ADAPTER_MISSING "adapter: $name"
        return 1
    fi
    
    source "$adapter_dir/$name/paths.sh"
    
    if [ -f "$adapter_dir/$name/commands.sh" ]; then
        source "$adapter_dir/$name/commands.sh"
    fi
    
    if [ -f "$adapter_dir/$name/tests.sh" ]; then
        source "$adapter_dir/$name/tests.sh"
    fi
    
    return 0
}

# Auto-detect and load the correct adapter
adapter_autoload() {
    local backend frontend
    backend="$(detect_backend 2>/dev/null)"
    frontend="$(detect_frontend 2>/dev/null)"
    
    local loaded=0
    
    if [ "$backend" != "unknown" ] && adapter_exists "$backend"; then
        adapter_load "$backend" && loaded=$((loaded + 1))
    fi
    
    if [ "$frontend" != "unknown" ] && [ "$frontend" != "$backend" ] && adapter_exists "$frontend"; then
        adapter_load "$frontend" && loaded=$((loaded + 1))
    fi
    
    return $loaded
}

# Validate adapter compatibility
adapter_validate() {
    local name="$1"
    local adapter_dir
    adapter_dir="$(get_adapter_dir)"
    
    if [ ! -d "$adapter_dir/$name" ]; then
        echo "Adapter not found: $name"
        return 1
    fi
    
    if [ ! -f "$adapter_dir/$name/paths.sh" ]; then
        echo "Adapter missing paths.sh: $name"
        return 1
    fi
    
    # Check for required functions
    source "$adapter_dir/$name/paths.sh"
    
    local required=("get_source_dir" "get_test_dir")
    for func in "${required[@]}"; do
        if ! type "$func" >/dev/null 2>&1; then
            echo "Adapter missing function: $func"
            return 1
        fi
    done
    
    return 0
}
