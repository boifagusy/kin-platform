#!/data/data/com.termux/files/usr/bin/bash

# ENGINEERING OS KERNEL — State-driven execution
# Every command begins here. No exceptions.

KERNEL_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SDK_ROOT="$(dirname "$KERNEL_DIR")"

# ── 1. Load Project State (mandatory) ──
kernel_load_state() {
    # Load state files in order
    for f in project.yaml session.yaml ai.yaml gate.yaml brick.yaml task.yaml; do
        if [ -f ".kin/state/$f" ]; then
            source "$KERNEL_DIR/state/loader.sh" 2>/dev/null
            kernel_read_state "$f"
        fi
    done
}

# ── 2. Validate State ──
kernel_validate_state() {
    local errors=0
    [ -f ".kin/state/project.yaml" ] || errors=$((errors + 1))
    [ -f ".kin/state/session.yaml" ] || errors=$((errors + 1))
    [ -f ".kin/state/gate.yaml" ] || errors=$((errors + 1))
    return $errors
}

# ── 3. Resume Project ──
kernel_resume() {
    echo ""
    echo "═══════════════════════════════════════"
    echo "  KERNEL — Project Resume"
    echo "═══════════════════════════════════════"
    
    local gate=$(grep "current_gate:" .kin/state/project.yaml 2>/dev/null | sed 's/.*: //')
    local brick=$(grep "active_brick:" .kin/state/project.yaml 2>/dev/null | sed 's/.*: //')
    local task=$(grep "name:" .kin/state/task.yaml 2>/dev/null | sed 's/.*: //')
    
    echo "  Gate:  ${gate:-0}"
    echo "  Brick: ${brick:-none}"
    echo "  Task:  ${task:-none}"
    echo ""
    echo "  State: $([ -f .kin/state/project.yaml ] && echo '✅ Loaded' || echo '❌ Missing')"
    echo "═══════════════════════════════════════"
}

# ── 4. Event Bus ──
kernel_emit_event() {
    local event="$1" data="${2:-}"
    local id=$(date +%s)
    local event_file=".kin/audit/$(printf '%06d' $id).yaml"
    
    mkdir -p .kin/audit
    cat > "$event_file" << YAML
event: $event
timestamp: $(date -u +%Y-%m-%dT%H:%M:%SZ)
data: $data
YAML
    
    # Notify engines that subscribe to this event
    for subscriber in .sdk/kernel/events/*.sh; do
        [ -f "$subscriber" ] && grep -q "# event: $event" "$subscriber" 2>/dev/null && bash "$subscriber" "$data" 2>/dev/null
    done
}

# ── 5. Snapshot ──
kernel_snapshot() {
    local id=$(date +%Y%m%d_%H%M%S)
    mkdir -p .kin/snapshots
    cp .kin/state/project.yaml ".kin/snapshots/${id}_project.yaml" 2>/dev/null
    cp .kin/state/gate.yaml ".kin/snapshots/${id}_gate.yaml" 2>/dev/null
    cp .kin/state/brick.yaml ".kin/snapshots/${id}_brick.yaml" 2>/dev/null
    echo "Snapshot: ${id}"
}

# ── 6. AI Startup Protocol ──
kernel_ai_startup() {
    echo ""
    echo "════════════════════════════════════════════"
    echo "  AI STARTUP PROTOCOL"
    echo "════════════════════════════════════════════"
    echo ""
    
    local steps=(
        "1. Load Constitution"
        "2. Load Registry"
        "3. Load Project State"
        "4. Validate State"
        "5. Load Active Brick"
        "6. Load Active Task"
        "7. Load Outstanding Bugs"
        "8. Compute Next Action"
        "9. Display Resume Report"
        "10. Wait for Engineering Manager"
    )
    
    for step in "${steps[@]}"; do
        echo "  $step"
    done
    
    echo ""
    echo "  Status: $(kernel_validate_state && echo '✅ Ready' || echo '❌ State Incomplete')"
    echo "════════════════════════════════════════════"
}

# ── Run kernel ──
kernel_load_state
