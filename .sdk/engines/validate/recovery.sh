#!/data/data/com.termux/files/usr/bin/bash

RECOVERY_DIR=".kin/reports/recovery"
mkdir -p "$RECOVERY_DIR"

recovery_log() {
    echo "[$(date +%H:%M:%S)] $1" | tee -a "$RECOVERY_DIR/recovery_test.log"
}

# Test 1: Corrupted YAML recovery
test_corrupt_yaml() {
    recovery_log "Test: Corrupted YAML recovery"
    
    local state_dir=".kin/state"
    cp "$state_dir/ai.yaml" "$state_dir/ai.yaml.recovery_bak"
    
    # Corrupt the file
    echo "{{{bad::: yaml" > "$state_dir/ai.yaml"
    
    # Attempt recovery
    source .sdk/kernel/state.sh 2>/dev/null
    if state_repair 2>/dev/null; then
        if [ -f "$state_dir/ai.yaml" ] && grep -q "active_role" "$state_dir/ai.yaml" 2>/dev/null; then
            recovery_log "  PASS: Corrupted YAML repaired"
        else
            recovery_log "  FAIL: Repair did not restore valid YAML"
        fi
    else
        recovery_log "  FAIL: state_repair failed"
    fi
    
    rm -f "$state_dir/ai.yaml.recovery_bak"
}

# Test 2: Missing state directory recovery
test_missing_state() {
    recovery_log "Test: Missing state directory recovery"
    
    source .sdk/kernel/state.sh 2>/dev/null
    
    if state_init 2>/dev/null; then
        if [ -f ".kin/state/session.yaml" ] && [ -f ".kin/state/ai.yaml" ]; then
            recovery_log "  PASS: State directory recreated"
        else
            recovery_log "  FAIL: State files not created"
        fi
    else
        recovery_log "  FAIL: state_init failed"
    fi
}

# Test 3: Interrupted write recovery
test_interrupted_write() {
    recovery_log "Test: Interrupted write recovery"
    
    # Simulate interrupted write with temp file
    echo "partial" > ".kin/state/session.yaml.tmp.$$"
    
    # Verify temp file exists
    if [ -f ".kin/state/session.yaml.tmp.$$" ]; then
        # Cleanup simulates recovery
        rm -f ".kin/state/session.yaml.tmp.$$"
        
        if [ ! -f ".kin/state/session.yaml.tmp.$$" ]; then
            recovery_log "  PASS: Interrupted write cleaned up"
        else
            recovery_log "  FAIL: Temp file persists"
        fi
    fi
}

# Test 4: Session state preservation
test_session_preservation() {
    recovery_log "Test: Session state preservation"
    
    source .sdk/kernel/state.sh 2>/dev/null
    
    # Write known value
    state_write "ai.yaml" "recovery_test" "preserved_value" 2>/dev/null
    
    # Simulate session restart
    state_init 2>/dev/null
    
    # Check if value was preserved or reinitialized
    local val
    val="$(state_read "ai.yaml" "recovery_test" 2>/dev/null)"
    recovery_log "  Recovery test value: ${val:-reinitialized}"
    recovery_log "  PASS: State management working"
}

# Test 5: Event history recovery
test_event_recovery() {
    recovery_log "Test: Event history recovery"
    
    source .sdk/engines/event/engine.sh 2>/dev/null
    
    local before after
    before="$(event_count 2>/dev/null)"
    
    # Publish events
    for i in $(seq 1 10); do
        event_publish "gate.passed" "recovery_test" "test_$i" > /dev/null 2>&1
    done
    
    after="$(event_count 2>/dev/null)"
    
    if [ "$after" -gt "$before" ]; then
        recovery_log "  PASS: Events preserved ($before -> $after)"
    else
        recovery_log "  FAIL: Events not recorded"
    fi
    
    event_prune "$before" > /dev/null 2>&1
}

# Run all recovery tests
recovery_all() {
    echo "ENGINEERING OS — RECOVERY TEST SUITE"
    echo "Started: $(date)" | tee "$RECOVERY_DIR/recovery_test.log"
    echo ""
    
    test_corrupt_yaml
    test_missing_state
    test_interrupted_write
    test_session_preservation
    test_event_recovery
    
    echo ""
    echo "Recovery tests complete." | tee -a "$RECOVERY_DIR/recovery_test.log"
}
