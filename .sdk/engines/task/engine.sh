#!/data/data/com.termux/files/usr/bin/bash

# TASK STATE MACHINE
# Planning → Implementation → Validation → Certification → Checkpoint → Closed

TASK_STATES=("planning" "implementation" "validation" "certification" "checkpoint" "closed")

task_state_valid() {
    local state="$1"
    for s in "${TASK_STATES[@]}"; do
        [ "$s" = "$state" ] && return 0
    done
    return 1
}

task_next_state() {
    local current="$1"
    case "$current" in
        planning)       echo "implementation" ;;
        implementation) echo "validation" ;;
        validation)     echo "certification" ;;
        certification)  echo "checkpoint" ;;
        checkpoint)     echo "closed" ;;
        closed)         echo "closed" ;;
        *)              echo "planning" ;;
    esac
}

task_status() {
    local task="${1:-current}"
    local state
    
    if [ -f ".kin/state/task.yaml" ]; then
        state="$(grep "state:" ".kin/state/task.yaml" 2>/dev/null | sed 's/.*: //')"
    else
        state="planning"
    fi
    
    echo "TASK STATE: $state"
    echo "Next: $(task_next_state "$state")"
}

task_advance() {
    local current next
    current="$(grep "state:" ".kin/state/task.yaml" 2>/dev/null | sed 's/.*: //')"
    current="${current:-planning}"
    next="$(task_next_state "$current")"
    
    if [ "$current" = "closed" ]; then
        echo "Task already closed."
        return 0
    fi
    
    # Validation gate — contract verification required before certification
    if [ "$next" = "certification" ]; then
        echo "Running contract verification..."
        if bash "$SDK_ROOT/engines/contracts/engine.sh" verify 2>/dev/null; then
            echo "✅ Contracts verified"
        else
            echo "❌ Contract verification failed — cannot certify"
            return 1
        fi
    fi
    
    mkdir -p .kin/state
    cat > .kin/state/task.yaml << YAML
task:
  state: $next
  updated: $(date -u +%Y-%m-%dT%H:%M:%SZ)
  previous: $current
YAML
    
    echo "Task: $current → $next"
    return 0
}

# Dispatch
case "${1:-status}" in
    status)   task_status "${2:-}" ;;
    advance)  task_advance ;;
    reset)    rm -f .kin/state/task.yaml; echo "Task reset to planning" ;;
    *)        echo "Usage: ai task [status|advance|reset]" ;;
esac
