#!/data/data/com.termux/files/usr/bin/bash

get_plugin_dir() {
    local sdk_root
    sdk_root="$(get_sdk_root)" || return 1
    echo "$sdk_root/commands/plugins"
}

load_plugin() {
    local plugin_name="$1"
    local plugin_dir
    plugin_dir="$(get_plugin_dir)" || return 1
    if [ -f "$plugin_dir/${plugin_name}.sh" ]; then
        source "$plugin_dir/${plugin_name}.sh"
        return 0
    else
        echo "❌ Plugin not found: $plugin_name" >&2
        return 1
    fi
}

list_plugins() {
    local plugin_dir
    plugin_dir="$(get_plugin_dir)" || return 1
    if [ -d "$plugin_dir" ]; then
        shopt -s nullglob
        local plugins=("$plugin_dir"/*.sh)
        shopt -u nullglob
        if [ ${#plugins[@]} -eq 0 ]; then
            echo "  (no plugins installed)"
        else
            for plugin in "${plugins[@]}"; do
                echo "  $(basename "$plugin" .sh)"
            done
        fi
    fi
    return 0
}

execute_plugin() {
    local plugin_name="$1"
    local function_name="${2:-main}"
    if load_plugin "$plugin_name"; then
        if type "${function_name}" >/dev/null 2>&1; then
            "${function_name}"
            return 0
        else
            echo "❌ Function not found: $function_name in $plugin_name" >&2
            return 1
        fi
    fi
}
