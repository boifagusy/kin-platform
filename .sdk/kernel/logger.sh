#!/data/data/com.termux/files/usr/bin/bash

# Log levels
readonly LOG_DEBUG=0
readonly LOG_INFO=1
readonly LOG_WARN=2
readonly LOG_ERROR=3

# Current log level
LOG_LEVEL=${LOG_LEVEL:-$LOG_INFO}

# Log directory
get_log_dir() {
    local project_root
    project_root="$(git rev-parse --show-toplevel 2>/dev/null)" || {
        echo "/tmp"
        return
    }
    echo "$project_root/.kin/history"
}

# Write log entry
_log() {
    local level="$1"
    local level_name="$2"
    local message="$3"
    
    if [ "$level" -ge "$LOG_LEVEL" ]; then
        local timestamp
        timestamp="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
        local log_dir
        log_dir="$(get_log_dir)"
        mkdir -p "$log_dir"
        
        echo "[$timestamp] [$level_name] $message" >> "$log_dir/kernel.log"
        
        if [ "$level" -ge "$LOG_WARN" ]; then
            echo "[$level_name] $message" >&2
        fi
    fi
}

log_debug() { _log "$LOG_DEBUG" "DEBUG" "$1"; }
log_info()  { _log "$LOG_INFO"  "INFO"  "$1"; }
log_warn()  { _log "$LOG_WARN"  "WARN"  "$1"; }
log_error() { _log "$LOG_ERROR" "ERROR" "$1"; }
