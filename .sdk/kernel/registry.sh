#!/data/data/com.termux/files/usr/bin/bash

# REGISTRY LOADER — Authoritative source of truth
# Reads REGISTRY.yaml, resolves plugins, validates filesystem

REGISTRY_FILE=".sdk/engines/REGISTRY.yaml"

# Load registry
registry_load() {
    if [ ! -f "$REGISTRY_FILE" ]; then
        echo "Registry not found: $REGISTRY_FILE" >&2
        return 1
    fi
    cat "$REGISTRY_FILE"
}

# Get all registered engine IDs
registry_engines() {
    grep "id:" "$REGISTRY_FILE" 2>/dev/null | sed 's/.*id: //'
}

# Resolve: given a command name, find its plugin file
registry_resolve() {
    local command="$1"
    
    # Direct plugin match
    if [ -f ".sdk/commands/plugins/${command}.sh" ]; then
        echo ".sdk/commands/plugins/${command}.sh"
        return 0
    fi
    
    # Check registry for dir mapping
    local dir
    dir=$(grep -A3 "id: $command" "$REGISTRY_FILE" 2>/dev/null | grep "dir:" | head -1 | sed 's/.*dir: //')
    if [ -n "$dir" ] && [ -f ".sdk/engines/${dir}/engine.sh" ]; then
        echo ".sdk/engines/${dir}/engine.sh"
        return 0
    fi
    
    return 1
}

# Validate: check for registry/filesystem mismatches
registry_validate() {
    local errors=0
    
    echo "REGISTRY VALIDATION"
    echo "═══════════════════════════════════════"
    echo ""
    
    # Check registered engines have directories or plugins
    for id in $(registry_engines); do
        local found=false
        [ -d ".sdk/engines/$id" ] && found=true
        [ -f ".sdk/commands/plugins/${id}.sh" ] && found=true
        
        # Check dir field in registry
        local dir
        dir=$(grep -A3 "id: $id" "$REGISTRY_FILE" 2>/dev/null | grep "dir:" | head -1 | sed 's/.*dir: //')
        [ -n "$dir" ] && [ -d ".sdk/engines/$dir" ] && found=true
        
        if ! $found; then
            echo "  ❌ Registered engine '$id' has no directory or plugin"
            errors=$((errors + 1))
        fi
    done
    
    # Check for plugins without registry entries
    for plugin in .sdk/commands/plugins/*.sh; do
        [ -f "$plugin" ] || continue
        local name=$(basename "$plugin" .sh)
        if ! grep -q "id: $name" "$REGISTRY_FILE" 2>/dev/null; then
            echo "  ⚠️  Plugin '$name' not in registry"
        fi
    done
    
    echo ""
    if [ $errors -eq 0 ]; then
        echo "  Registry: ✅ VALID"
    else
        echo "  Registry: ❌ $errors issue(s)"
    fi
    
    return $errors
}

# Count registered plugins
registry_plugin_count() {
    ls .sdk/commands/plugins/*.sh 2>/dev/null | wc -l | tr -d ' '
}

# Count registered engines
registry_engine_count() {
    grep -c "id:" "$REGISTRY_FILE" 2>/dev/null
}
