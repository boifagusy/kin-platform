#!/data/data/com.termux/files/usr/bin/bash

if [ -n "$SDK_ROOT" ]; then
    KERNEL_DIR="$SDK_ROOT/kernel"
    ENGINES_DIR="$SDK_ROOT/engines"
else
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    ENGINES_DIR="$(dirname "$SCRIPT_DIR")"
    KERNEL_DIR="$(dirname "$ENGINES_DIR")/kernel"
fi

source "$KERNEL_DIR/common.sh" 2>/dev/null || true
source "$KERNEL_DIR/logger.sh" 2>/dev/null || true
source "$KERNEL_DIR/filesystem.sh" 2>/dev/null || true
source "$KERNEL_DIR/yaml.sh" 2>/dev/null || true
source "$KERNEL_DIR/state.sh" 2>/dev/null || true

# Get audit directory
get_audit_dir() {
    local root
    root="$(get_project_root 2>/dev/null)" || root="$HOME"
    echo "$root/.kin/history/audit"
}

# Create an audit record
audit_record() {
    local action="$1"
    local agent="${2:-unknown}"
    local details="${3:-}"
    local files="${4:-}"
    local now
    now="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
    local audit_id="audit_$(date +%s)_$$"
    
    local audit_dir
    audit_dir="$(get_audit_dir)"
    
    # Organize by month
    local month_dir="$audit_dir/$(date +%Y-%m)"
    ensure_dir "$month_dir"
    
    local audit_file="$month_dir/${audit_id}.yaml"
    
    # Get current context
    local gate brick role
    gate="$(state_read "gate.yaml" "current" 2>/dev/null | tr -d ' ')"
    brick="$(state_read "brick.yaml" "active_brick" 2>/dev/null | tr -d ' ')"
    role="$(state_read "ai.yaml" "active_role" 2>/dev/null | tr -d ' ')"
    local session_id
    session_id="$(state_read "session.yaml" "session_id" 2>/dev/null | tr -d ' ')"
    
    cat > "$audit_file" <<YAML
audit:
  id: $audit_id
  timestamp: $now
  session: ${session_id:-unknown}
  agent:
    role: ${role:-unassigned}
    name: $agent
  action: $action
  details: $details
  files: $files
  context:
    gate: ${gate:-0}
    brick: ${brick:-none}
YAML
    
    log_debug "Audit: $action by $agent"
    echo "$audit_id"
    return 0
}

# Quick audit for common actions
audit_gate_change() { audit_record "gate_changed" "${1:-system}" "Gate transition" ""; }
audit_brick_change() { audit_record "brick_modified" "${1:-system}" "Brick: ${2:-unknown}" "${3:-}"; }
audit_session_start() { audit_record "session_started" "${1:-system}" "Session started" ""; }
audit_session_end() { audit_record "session_ended" "${1:-system}" "Session ended" ""; }
audit_role_change() { audit_record "role_changed" "${1:-system}" "Role: ${2:-unknown}" ""; }
audit_docs_update() { audit_record "docs_updated" "${1:-system}" "Docs: ${2:-unknown}" "${3:-}"; }
audit_test_run() { audit_record "test_executed" "${1:-system}" "Tests: ${2:-}" ""; }

# List recent audit records
audit_list() {
    local limit="${1:-20}"
    local audit_dir
    audit_dir="$(get_audit_dir)"
    
    echo "AUDIT TRAIL"
    echo "═══════════════════════════════════════"
    
    if [ ! -d "$audit_dir" ]; then
        echo "  (no audit records)"
        return
    fi
    
    local count=0
    # Find all audit files sorted by name (which includes timestamp)
    find "$audit_dir" -name "audit_*.yaml" -type f 2>/dev/null | sort -r | head -"$limit" | while read f; do
        local action timestamp agent gate
        action="$(yaml_get_nested "$f" "audit" "action" 2>/dev/null)"
        timestamp="$(yaml_get_nested "$f" "audit" "timestamp" 2>/dev/null)"
        agent="$(yaml_get_nested "$f" "audit" "agent" "name" 2>/dev/null)"
        gate="$(yaml_get_nested "$f" "audit" "context" "gate" 2>/dev/null)"
        echo "  [$timestamp] $action (gate:$gate) by $agent"
    done
}

# Query audit by field
audit_query() {
    local field="$1"
    local value="$2"
    local audit_dir
    audit_dir="$(get_audit_dir)"
    
    echo "Audit query: $field=$value"
    echo "═══════════════════════════════════════"
    
    find "$audit_dir" -name "audit_*.yaml" -type f 2>/dev/null | while read f; do
        if grep -q "$value" "$f" 2>/dev/null; then
            local action timestamp
            action="$(yaml_get_nested "$f" "audit" "action" 2>/dev/null)"
            timestamp="$(yaml_get_nested "$f" "audit" "timestamp" 2>/dev/null)"
            echo "  $timestamp: $action"
        fi
    done
}

# Count audit records
audit_count() {
    local audit_dir
    audit_dir="$(get_audit_dir)"
    find "$audit_dir" -name "audit_*.yaml" -type f 2>/dev/null | wc -l | tr -d ' '
}

# Verify audit integrity
audit_verify() {
    local audit_dir
    audit_dir="$(get_audit_dir)"
    local errors=0
    
    echo "Audit integrity check"
    echo "═══════════════════════════════════════"
    
    if [ ! -d "$audit_dir" ]; then
        echo "  No audit directory"
        return 1
    fi
    
    # Check for gaps - if session started, should have corresponding events
    local session_active
    session_active="$(state_read "session.yaml" "status" 2>/dev/null | tr -d ' ')"
    
    if [ "$session_active" = "active" ]; then
        local session_start_audited
        session_start_audited="$(audit_query "action" "session_started" 2>/dev/null | head -1)"
        if [ -z "$session_start_audited" ]; then
            echo "  ⚠️  Active session has no start audit record"
            errors=$((errors + 1))
        fi
    fi
    
    # Check file count
    local count
    count="$(audit_count)"
    echo "  Total records: $count"
    
    if [ $errors -eq 0 ]; then
        echo "  Audit integrity: OK"
    else
        echo "  Audit integrity: $errors issue(s)"
    fi
    
    return $errors
}
