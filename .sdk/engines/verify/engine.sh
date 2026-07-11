#!/data/data/com.termux/files/usr/bin/bash

# Verification Engine
# Automated, repeatable verification for all gates

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
KERNEL_DIR="$(dirname "$(dirname "$SCRIPT_DIR")")/kernel"

source "$KERNEL_DIR/common.sh" 2>/dev/null || true
source "$KERNEL_DIR/errors.sh" 2>/dev/null || true
source "$KERNEL_DIR/logger.sh" 2>/dev/null || true
source "$KERNEL_DIR/filesystem.sh" 2>/dev/null || true
source "$KERNEL_DIR/state.sh" 2>/dev/null || true
source "$KERNEL_DIR/yaml.sh" 2>/dev/null || true

# Report directory
get_report_dir() {
    local root
    root="$(get_project_root 2>/dev/null)" || root="$HOME"
    echo "$root/.kin/reports"
}

# Initialize a verification report
verify_init() {
    local gate="$1"
    local report_dir
    report_dir="$(get_report_dir)"
    ensure_dir "$report_dir"
    
    REPORT_FILE="$report_dir/${gate}_report.yaml"
    METRICS_FILE="$report_dir/${gate}_metrics.yaml"
    AUDIT_FILE="$report_dir/${gate}_audit.yaml"
    ROLLBACK_FILE="$report_dir/${gate}_rollback.yaml"
    
    local now
    now="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
    
    atomic_write "$REPORT_FILE" \
"gate: $gate
started: $now
status: in_progress
tests: []
passed: 0
failed: 0
skipped: 0"
    
    atomic_write "$METRICS_FILE" \
"gate: $gate
started: $now
measurements: {}"
    
    atomic_write "$AUDIT_FILE" \
"gate: $gate
started: $now
events: []"
    
    atomic_write "$ROLLBACK_FILE" \
"gate: $gate
started: $now
rollback_tested: false
rollback_passed: false"
}

# Record a test result
verify_test() {
    local name="$1"
    local result="$2"  # pass, fail, skip
    local detail="${3:-}"
    local now
    now="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
    
    # Update report
    local entry="  - name: $name
    result: $result
    time: $now"
    [ -n "$detail" ] && entry="$entry
    detail: $detail"
    
    echo "$entry" >> "$REPORT_FILE"
    
    # Update counts
    case "$result" in
        pass) sed -i "s/passed:.*/passed: $(( $(grep "passed:" "$REPORT_FILE" | sed 's/.*: //') + 1 ))/" "$REPORT_FILE" ;;
        fail) sed -i "s/failed:.*/failed: $(( $(grep "failed:" "$REPORT_FILE" | sed 's/.*: //') + 1 ))/" "$REPORT_FILE" ;;
        skip) sed -i "s/skipped:.*/skipped: $(( $(grep "skipped:" "$REPORT_FILE" | sed 's/.*: //') + 1 ))/" "$REPORT_FILE" ;;
    esac
    
    # Print result
    case "$result" in
        pass) echo "   ✅ $name" ;;
        fail) echo "   ❌ $name" ;;
        skip) echo "   ⏭️  $name" ;;
    esac
}

# Record a metric
verify_metric() {
    local name="$1"
    local value="$2"
    local unit="${3:-ms}"
    local now
    now="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
    
    local entry="  - name: $name
    value: $value
    unit: $unit
    time: $now"
    
    echo "$entry" >> "$METRICS_FILE"
    echo "   📊 $name: $value $unit"
}

# Record an audit event
verify_audit() {
    local action="$1"
    local detail="${2:-}"
    local now
    now="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
    
    echo "  - time: $now
    action: $action
    detail: $detail" >> "$AUDIT_FILE"
}

# Record rollback test
verify_rollback() {
    local resource="$1"
    local result="$2"
    local now
    now="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
    
    sed -i "s/rollback_tested:.*/rollback_tested: true/" "$ROLLBACK_FILE"
    sed -i "s/rollback_passed:.*/rollback_passed: $result/" "$ROLLBACK_FILE"
    
    if [ "$result" = "true" ]; then
        echo "   ✅ Rollback verified: $resource"
    else
        echo "   ❌ Rollback failed: $resource"
    fi
}

# Finalize report
verify_finalize() {
    local passed
    local failed
    passed="$(grep "passed:" "$REPORT_FILE" | sed 's/.*: //')"
    failed="$(grep "failed:" "$REPORT_FILE" | sed 's/.*: //')"
    
    local status="passed"
    if [ "${failed:-0}" -gt 0 ]; then
        status="failed"
    fi
    
    local now
    now="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
    
    sed -i "s/status:.*/status: $status/" "$REPORT_FILE"
    sed -i "s/started:.*/completed: $now/" "$REPORT_FILE"
    
    echo ""
    echo "════════════════════════════════════════════"
    echo "  VERIFICATION: $status ($passed passed, $failed failed)"
    echo "════════════════════════════════════════════"
    
    [ "$status" = "passed" ] && return 0 || return 1
}

# Time a command execution
verify_time() {
    local name="$1"
    shift
    local start end duration
    start=$(date +%s%N 2>/dev/null || echo 0)
    "$@" >/dev/null 2>&1
    local exit_code=$?
    end=$(date +%s%N 2>/dev/null || echo 0)
    duration=$(( (end - start) / 1000000 ))
    verify_metric "$name" "$duration" "ms"
    return $exit_code
}
