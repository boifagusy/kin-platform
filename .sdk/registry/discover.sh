#!/data/data/com.termux/files/usr/bin/bash
# Engine Discovery System
# Automatically finds and registers all engines

SDK_DIR="${KIN_SDK_DIR:-$HOME/kin_project/.sdk}"
REGISTRY_FILE="$SDK_DIR/registry/engines.json"

discover_engines() {
    echo "Discovering engines..."
    
    # Initialize registry
    echo '{"engines": {}, "plugins": {}, "last_discovery": null}' > "$REGISTRY_FILE"
    
    # Scan engine directories
    for engine_dir in "$SDK_DIR"/engines/*/; do
        [ -d "$engine_dir" ] || continue
        
        local engine_name
        engine_name=$(basename "$engine_dir")
        local engine_config="$engine_dir/engine.yaml"
        
        if [ -f "$engine_config" ]; then
            # Parse YAML config (using PHP for reliability)
            php -r "
            \$yaml = file_get_contents('$engine_config');
            // Simple YAML parsing for our known structure
            \$data = [];
            foreach (explode(\"\\n\", \$yaml) as \$line) {
                if (preg_match('/^(\w+):\s*(.+)/', \$line, \$m)) {
                    \$data[\$m[1]] = trim(\$m[2], '\"\\'');
                }
            }
            
            \$registry = json_decode(file_get_contents('$REGISTRY_FILE'), true);
            \$registry['engines']['$engine_name'] = [
                'name' => \$data['name'] ?? '$engine_name',
                'version' => \$data['version'] ?? '1.0.0',
                'command' => \$data['command'] ?? '$engine_name',
                'depends_on' => explode(',', \$data['depends_on'] ?? ''),
                'path' => '$engine_dir'
            ];
            file_put_contents('$REGISTRY_FILE', json_encode(\$registry, JSON_PRETTY_PRINT));
            " 2>/dev/null || {
                # Fallback: basic registration
                php -r "
                \$registry = json_decode(file_get_contents('$REGISTRY_FILE'), true);
                \$registry['engines']['$engine_name'] = [
                    'name' => '$engine_name',
                    'version' => '1.0.0',
                    'command' => '$engine_name',
                    'path' => '$engine_dir'
                ];
                file_put_contents('$REGISTRY_FILE', json_encode(\$registry, JSON_PRETTY_PRINT));
                "
            }
            
            echo "  ✓ $engine_name"
        fi
    done
    
    # Scan plugins
    for plugin_file in "$SDK_DIR"/plugins/*.json; do
        [ -f "$plugin_file" ] || continue
        
        local plugin_name
        plugin_name=$(basename "$plugin_file" .json)
        
        php -r "
        \$plugin = json_decode(file_get_contents('$plugin_file'), true);
        \$registry = json_decode(file_get_contents('$REGISTRY_FILE'), true);
        \$registry['plugins']['$plugin_name'] = \$plugin;
        file_put_contents('$REGISTRY_FILE', json_encode(\$registry, JSON_PRETTY_PRINT));
        " 2>/dev/null
        
        echo "  ✓ plugin: $plugin_name"
    done
    
    # Update timestamp
    php -r "
    \$registry = json_decode(file_get_contents('$REGISTRY_FILE'), true);
    \$registry['last_discovery'] = date('c');
    file_put_contents('$REGISTRY_FILE', json_encode(\$registry, JSON_PRETTY_PRINT));
    "
    
    echo ""
    echo "Registry: $REGISTRY_FILE"
}

list_engines() {
    if [ -f "$REGISTRY_FILE" ]; then
        php -r "
        \$registry = json_decode(file_get_contents('$REGISTRY_FILE'), true);
        echo \"Registered Engines:\\n\";
        foreach (\$registry['engines'] as \$name => \$engine) {
            echo \"  - \$name v{\$engine['version']}: {\$engine['command']}\\n\";
        }
        echo \"\\nRegistered Plugins:\\n\";
        foreach (\$registry['plugins'] as \$name => \$plugin) {
            echo \"  - \$name v{\$plugin['version']}\\n\";
        }
        " 2>/dev/null || cat "$REGISTRY_FILE"
    else
        echo "No registry found. Run 'ai engine discover' first."
    fi
}

# Main
case "${1:-discover}" in
    discover) discover_engines ;;
    list) list_engines ;;
    *) echo "Usage: ai engine [discover|list]" ;;
esac
