#!/data/data/com.termux/files/usr/bin/bash

get_project_root() {
    local root
    root="$(git rev-parse --show-toplevel 2>/dev/null)"
    if [ -z "$root" ]; then
        echo "❌ Not inside a Git project" >&2
        return 1
    fi
    echo "$root"
}

get_sdk_root() {
    if [ -n "$SDK_ROOT" ] && [ -f "$SDK_ROOT/sdk.yaml" ]; then
        echo "$SDK_ROOT"
        return 0
    fi
    local project_root
    project_root="$(get_project_root)" || return 1
    if [ -d "$project_root/.sdk" ]; then
        echo "$project_root/.sdk"
    else
        echo "❌ SDK not found" >&2
        return 1
    fi
}

get_state_dir() {
    local project_root
    project_root="$(get_project_root)" || return 1
    echo "$project_root/.kin/state"
}

is_termux() {
    [ -d "/data/data/com.termux" ] && return 0 || return 1
}

write_state() {
    local file="$1"
    local content="$2"
    local dir
    dir="$(dirname "$file")"
    mkdir -p "$dir"
    printf '%s\n' "$content" > "$file"
}

read_state() {
    local file="$1"
    if [ -f "$file" ]; then
        cat "$file"
    fi
}
