#!/data/data/com.termux/files/usr/bin/bash

# Source kernel modules
KERNEL_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

source "$KERNEL_DIR/errors.sh" 2>/dev/null || true
source "$KERNEL_DIR/logger.sh" 2>/dev/null || true

# Check if running in Termux
is_termux() {
    [ -d "/data/data/com.termux" ] && return 0 || return 1
}

# Get SDK root from KERNEL_DIR
get_sdk_root() {
    dirname "$KERNEL_DIR"
}

# Get SDK version
get_sdk_version() {
    local sdk_root
    sdk_root="$(get_sdk_root)"
    if [ -f "$sdk_root/sdk.yaml" ]; then
        grep "^  version:" "$sdk_root/sdk.yaml" | head -1 | sed 's/.*: *//'
    else
        echo "0.0.0"
    fi
}

# Validate required command exists
require_cmd() {
    local cmd="$1"
    if ! command -v "$cmd" >/dev/null 2>&1; then
        echo "Required command not found: $cmd" >&2
        return 1
    fi
    return 0
}

# Check minimum bash version
require_bash() {
    local major="${1:-5}"
    if [ "${BASH_VERSINFO[0]}" -lt "$major" ]; then
        echo "Bash $major.0+ required, found ${BASH_VERSION}" >&2
        return 1
    fi
    return 0
}
