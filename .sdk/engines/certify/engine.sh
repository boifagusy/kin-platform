#!/data/data/com.termux/files/usr/bin/bash

# TASK CERTIFICATION ENGINE
# Produces machine-readable certification records consumed by all engines

if [ -n "$SDK_ROOT" ]; then
    KERNEL_DIR="$SDK_ROOT/kernel"
    ENGINES_DIR="$SDK_ROOT/engines"
else
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    ENGINES_DIR="$(dirname "$SCRIPT_DIR")"
    KERNEL_DIR="$(dirname "$ENGINES_DIR")/kernel"
fi

source "$KERNEL_DIR/state.sh" 2>/dev/null
source "$KERNEL_DIR/yaml.sh" 2>/dev/null
source "$KERNEL_DIR/filesystem.sh" 2>/dev/null
source "$ENGINES_DIR/gate/engine.sh" 2>/dev/null

CERT_DIR=".kin/certifications"

# ── Create a certification record ──
certify_task() {
    local task_id="${1:-unknown}"
    local task_name="${2:-unknown}"
    local status="${3:-CERTIFIED}"
    local files_created="${4:-0}"
    local files_modified="${5:-0}"
    local validation_score="${6:-0}"
    local validation_total="${7:-0}"
    local rollback="${8:-}"
    
    local now; now="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
    local project; project="$(get_project_root 2>/dev/null | xargs basename 2>/dev/null)"
    local gate; gate="$(gate_current 2>/dev/null)"
    local gate_name; gate_name="$(gate_name "$gate" 2>/dev/null)"
    local brick; brick="$(state_read "brick.yaml" "active_brick" 2>/dev/null | tr -d ' ')"
    local sdk_ver; sdk_ver="$(grep "version:" .sdk/sdk.yaml 2>/dev/null | head -1 | sed 's/.*: //')"
    
    local cert_file="$CERT_DIR/task_${task_id}_$(date +%Y%m%d_%H%M%S).txt"
    mkdir -p "$CERT_DIR"
    
    # Build the certification record
    cat > "$cert_file" << EOF
══════════════════════════════════════
TASK CERTIFICATION RECORD
══════════════════════════════════════

Project:        ${project}
SDK Version:    ${sdk_ver}
Gate:           ${gate} — ${gate_name}
Brick:          ${brick:-none}

Task ID:        ${task_id}
Task Name:      ${task_name}
Phase:          Implementation

──────────────────────────────────────

Execution
---------
Certified At:     ${now}
Status:           ${status}

Files Created:    ${files_created}
Files Modified:   ${files_modified}

──────────────────────────────────────

Validation
----------
Score:            ${validation_score} / ${validation_total}

──────────────────────────────────────

Rollback
--------
${rollback:-No rollback steps defined}

──────────────────────────────────────

Certification
-------------
Certified By:    Engineering OS
Timestamp:       ${now}
Status:          APPROVED

──────────────────────────────────────
EOF
    
    # Also save machine-readable YAML
    cat > "${cert_file}.yaml" << YAML
certification:
  task_id: $task_id
  task_name: $task_name
  project: $project
  gate: $gate
  brick: ${brick:-none}
  status: $status
  timestamp: $now
  files_created: $files_created
  files_modified: $files_modified
  validation_score: $validation_score
  validation_total: $validation_total
YAML
    
    echo ""
    cat "$cert_file"
    echo ""
    echo "  Machine-readable: ${cert_file}.yaml"
}

# ── List all certifications ──
certify_list() {
    echo "TASK CERTIFICATIONS"
    echo "═══════════════════════════════════════"
    
    if [ -d "$CERT_DIR" ] && [ -n "$(ls -A "$CERT_DIR" 2>/dev/null)" ]; then
        for f in "$CERT_DIR"/*.yaml; do
            [ -f "$f" ] || continue
            local id name status ts
            id="$(grep "task_id:" "$f" 2>/dev/null | sed 's/.*: //')"
            name="$(grep "task_name:" "$f" 2>/dev/null | sed 's/.*: //')"
            status="$(grep "status:" "$f" 2>/dev/null | sed 's/.*: //')"
            ts="$(grep "timestamp:" "$f" 2>/dev/null | sed 's/.*: //')"
            echo "  Task $id: $name — $status ($ts)"
        done
    else
        echo "  (no certifications yet)"
    fi
}

# ── Show gate progress from certifications ──
certify_gate_progress() {
    local gate; gate="$(gate_current 2>/dev/null)"
    
    echo "GATE $gate PROGRESS"
    echo "═══════════════════════════════════════"
    
    local total=0 certified=0
    if [ -d "$CERT_DIR" ]; then
        for f in "$CERT_DIR"/*.yaml; do
            [ -f "$f" ] || continue
            local g; g="$(grep "gate:" "$f" 2>/dev/null | sed 's/.*: //')"
            local s; s="$(grep "status:" "$f" 2>/dev/null | sed 's/.*: //')"
            if [ "$g" = "$gate" ]; then
                total=$((total + 1))
                [ "$s" = "CERTIFIED" ] && certified=$((certified + 1))
            fi
        done
    fi
    
    if [ $total -gt 0 ]; then
        local pct=$((certified * 100 / total))
        echo "  Tasks: $certified / $total certified ($pct%)"
        echo "  Gate completion: $pct%"
    else
        echo "  No tasks certified for this gate yet"
    fi
}

# Dispatch
case "${1:-list}" in
    create)
        certify_task "${2:-}" "${3:-}" "${4:-CERTIFIED}" "${5:-0}" "${6:-0}" "${7:-0}" "${8:-0}" "${9:-}" ;;
    list)   certify_list ;;
    progress) certify_gate_progress ;;
    *)
        echo "Usage: ai certify [create|list|progress]"
        certify_list
        ;;
esac
