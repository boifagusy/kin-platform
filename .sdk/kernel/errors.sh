#!/data/data/com.termux/files/usr/bin/bash

# Error codes
readonly E_GIT_NOT_FOUND=1
readonly E_SDK_NOT_FOUND=2
readonly E_STATE_CORRUPT=3
readonly E_YAML_PARSE=4
readonly E_FILE_LOCKED=5
readonly E_ADAPTER_MISSING=6
readonly E_PLUGIN_FAILED=7
readonly E_PERMISSION_DENIED=8
readonly E_GATE_BLOCKED=9

# Error messages
error_msg() {
    local code="$1"
    case "$code" in
        $E_GIT_NOT_FOUND)     echo "Not inside a Git repository" ;;
        $E_SDK_NOT_FOUND)     echo "SDK not found" ;;
        $E_STATE_CORRUPT)     echo "State file is corrupt" ;;
        $E_YAML_PARSE)        echo "Failed to parse YAML" ;;
        $E_FILE_LOCKED)       echo "File is locked by another agent" ;;
        $E_ADAPTER_MISSING)   echo "Required adapter not found" ;;
        $E_PLUGIN_FAILED)     echo "Plugin failed to load" ;;
        $E_PERMISSION_DENIED) echo "Permission denied" ;;
        $E_GATE_BLOCKED)      echo "Gate progression is blocked" ;;
        *)                    echo "Unknown error (code: $code)" ;;
    esac
}

# Exit with error
throw() {
    local code="$1"
    local msg="${2:-$(error_msg "$code")}"
    echo "ERROR: $msg" >&2
    return "$code"
}
