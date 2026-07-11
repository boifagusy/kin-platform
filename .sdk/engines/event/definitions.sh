#!/data/data/com.termux/files/usr/bin/bash

# Valid event types
readonly EVENT_TYPES=(
    "gate.started"
    "gate.passed"
    "gate.failed"
    "gate.blocked"
    "gate.unblocked"
    "brick.created"
    "brick.status_changed"
    "brick.locked"
    "brick.unlocked"
    "session.started"
    "session.ended"
    "role.changed"
    "workflow.updated"
    "test.started"
    "test.passed"
    "test.failed"
    "docs.updated"
    "git.commit"
    "git.branch"
    "audit.recorded"
    "knowledge.captured"
)

# Validate event type
event_type_valid() {
    local type="$1"
    for valid in "${EVENT_TYPES[@]}"; do
        [ "$type" = "$valid" ] && return 0
    done
    return 1
}
