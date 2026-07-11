#!/data/data/com.termux/files/usr/bin/bash

# Atomic write - write to temp file then rename
atomic_write() {
    local file="$1"
    local content="$2"
    local dir
    dir="$(dirname "$file")"
    mkdir -p "$dir"
    local tmp="${file}.tmp.$$"
    printf '%s\n' "$content" > "$tmp" || {
        rm -f "$tmp"
        return 1
    }
    mv "$tmp" "$file" || {
        rm -f "$tmp"
        return 1
    }
}

# Safe read - return file contents or empty
safe_read() {
    local file="$1"
    if [ -f "$file" ] && [ -r "$file" ]; then
        cat "$file"
    fi
}

# Check if file exists and is non-empty
file_not_empty() {
    [ -f "$1" ] && [ -s "$1" ]
}

# Create directory if missing
ensure_dir() {
    local dir="$1"
    if [ ! -d "$dir" ]; then
        mkdir -p "$dir" || return 1
    fi
}

# Lock file for exclusive access (simple PID-based)
acquire_lock() {
    local lockfile="$1"
    local pid="$2"
    if [ -f "$lockfile" ]; then
        local holder
        holder="$(cat "$lockfile" 2>/dev/null)"
        if kill -0 "$holder" 2>/dev/null; then
            return 1  # Lock held
        fi
    fi
    echo "$pid" > "$lockfile"
    return 0
}

# Release lock
release_lock() {
    local lockfile="$1"
    rm -f "$lockfile"
}

# Check if path is within project
is_within_project() {
    local path="$1"
    local project_root
    project_root="$(git rev-parse --show-toplevel 2>/dev/null)" || return 1
    case "$(cd "$(dirname "$path")" 2>/dev/null && pwd)" in
        "$project_root"|"$project_root/"*) return 0 ;;
        *) return 1 ;;
    esac
}
