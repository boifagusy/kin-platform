# Event: *
# Subscriber: Knowledge Engine

handle_event() {
    local type="$1"
    local event_file="$2"
    
    local knowledge_engine
    if [ -n "$SDK_ROOT" ]; then
        knowledge_engine="$SDK_ROOT/engines/knowledge/engine.sh"
    else
        knowledge_engine="$(dirname "$(dirname "${BASH_SOURCE[0]}")")/knowledge/engine.sh"
    fi
    
    [ -f "$knowledge_engine" ] && source "$knowledge_engine" 2>/dev/null
    
    case "$type" in
        test.failed)
            knowledge_add_lesson "test_failure" "Test failed in $(date +%Y-%m-%d)" "Review and fix" 2>/dev/null || true ;;
        brick.completed)
            knowledge_add_lesson "brick_completed" "Brick completed" "Document lessons learned" 2>/dev/null || true ;;
    esac
}
