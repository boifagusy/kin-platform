# Event: *
# Subscriber: Audit Engine

handle_event() {
    local type="$1"
    local event_file="$2"
    
    # Source audit engine
    local audit_engine
    if [ -n "$SDK_ROOT" ]; then
        audit_engine="$SDK_ROOT/engines/audit/engine.sh"
    else
        audit_engine="$(dirname "$(dirname "${BASH_SOURCE[0]}")")/audit/engine.sh"
    fi
    
    [ -f "$audit_engine" ] && source "$audit_engine" 2>/dev/null
    
    # Map event to audit action
    case "$type" in
        gate.*)          audit_gate_change "event" ;;
        brick.*)         audit_brick_change "event" "brick_event" "" ;;
        session.started) audit_session_start "event" ;;
        session.ended)   audit_session_end "event" ;;
        role.changed)    audit_role_change "event" "role_event" ;;
        docs.updated)    audit_docs_update "event" "docs_event" "" ;;
        test.*)          audit_test_run "event" "test_event" ;;
    esac
}
