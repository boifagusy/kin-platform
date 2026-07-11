# Event: *
# Subscriber: Git Engine

handle_event() {
    local type="$1"
    local event_file="$2"
    
    local git_engine
    if [ -n "$SDK_ROOT" ]; then
        git_engine="$SDK_ROOT/engines/git/engine.sh"
    else
        git_engine="$(dirname "$(dirname "${BASH_SOURCE[0]}")")/git/engine.sh"
    fi
    
    [ -f "$git_engine" ] && source "$git_engine" 2>/dev/null
    
    case "$type" in
        gate.passed)
            # Auto-commit on gate completion
            local gate_num
            gate_num="$(yaml_get_nested "$event_file" "event" "data" 2>/dev/null)"
            if git_is_dirty 2>/dev/null; then
                git_commit "Gate $gate_num completed" 2>/dev/null || true
            fi ;;
    esac
}
