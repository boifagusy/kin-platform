# Description: Engineering Manager Console — the only command you need
# Requires: state gate workflow validate git brick knowledge audit

work_main() {
    # Load all engines
    source "$SDK_ROOT/engines/gate/engine.sh" 2>/dev/null
    source "$SDK_ROOT/engines/workflow/engine.sh" 2>/dev/null
    source "$SDK_ROOT/engines/validate/engine.sh" 2>/dev/null
    source "$SDK_ROOT/engines/git/engine.sh" 2>/dev/null
    source "$SDK_ROOT/engines/brick/engine.sh" 2>/dev/null
    source "$SDK_ROOT/engines/audit/engine.sh" 2>/dev/null
    source "$SDK_ROOT/engines/knowledge/engine.sh" 2>/dev/null
    source "$SDK_ROOT/kernel/state.sh" 2>/dev/null
    
    # Gather state
    local project_name gate gate_name brick role session_status
    local sdk_version health_score branch changes
    local waiting blocked blocked_reason
    
    project_name="$(basename "$(get_project_root 2>/dev/null)")"
    sdk_version="$(grep "version:" "$SDK_ROOT/sdk.yaml" 2>/dev/null | head -1 | sed 's/.*: //')"
    gate="$(gate_current 2>/dev/null)"
    gate_name="$(gate_name "$gate" 2>/dev/null)"
    brick="$(state_read "brick.yaml" "active_brick" 2>/dev/null | tr -d ' ')"
    role="$(state_read "ai.yaml" "active_role" 2>/dev/null | tr -d ' ')"
    session_status="$(state_read "session.yaml" "status" 2>/dev/null | tr -d ' ')"
    waiting="$(state_read "ai.yaml" "waiting_for" 2>/dev/null | tr -d ' ')"
    blocked="$(state_read "gate.yaml" "blocked" 2>/dev/null | tr -d ' ')"
    blocked_reason="$(state_read "gate.yaml" "blocked_reason" 2>/dev/null | tr -d ' ')"
    branch="$(git_branch 2>/dev/null)"
    changes=$(git status --porcelain 2>/dev/null | wc -l | tr -d ' ')
    health_score=$(validate_project 2>/dev/null | grep "Score:" | tr -d ' ' | sed 's/Score://')
    
    clear 2>/dev/null || true
    
    # HEADER
    cat << HEADER
╔══════════════════════════════════════════════════════════════╗
║              ENGINEERING OS — MANAGEMENT CONSOLE             ║
╠══════════════════════════════════════════════════════════════╣
║ Project:  ${project_name}                                      ║
║ SDK:      v${sdk_version}                                          ║
║ Health:   ${health_score:-?}%                                            ║
║ Git:      ${branch} (${changes} files)                                  ║
║ Session:  ${session_status:-inactive}                                          ║
╠══════════════════════════════════════════════════════════════╣
║                                                            ║
║  GATE ${gate} — ${gate_name}                                         ║
║  BRICK: ${brick:-none}                                             ║
║  ROLE:  ${role:-unassigned}                                          ║
║                                                            ║
HEADER

    # INBOX — Pending Decisions & Blockers
    echo "╠══════════════════════════════════════════════════════════════╣"
    echo "║  📋 INBOX                                                  ║"
    
    local inbox_count=0
    
    # Blockers
    if [ "$blocked" = "true" ]; then
        echo "║  ⛔ BLOCKED: ${blocked_reason}                              ║"
        inbox_count=$((inbox_count + 1))
    fi
    
    # Pending approvals (Gates 3 and 11 require approval)
    if [ "$gate" = "3" ] || [ "$gate" = "11" ]; then
        echo "║  ✋ APPROVAL NEEDED: Gate $gate — $(gate_name "$gate")       ║"
        inbox_count=$((inbox_count + 1))
    fi
    
    # Dirty git
    if [ "${changes:-0}" -gt 10 ]; then
        echo "║  💾 UNCOMMITTED: $changes files changed                     ║"
        inbox_count=$((inbox_count + 1))
    fi
    
    # No active brick
    if [ "${brick:-none}" = "none" ] && [ "$gate" -ge 5 ]; then
        echo "║  🧱 NO ACTIVE BRICK — ai brick create <name>               ║"
        inbox_count=$((inbox_count + 1))
    fi
    
    # No role assigned
    if [ "${role:-unassigned}" = "unassigned" ]; then
        echo "║  👤 NO ROLE — ai role set <role>                           ║"
        inbox_count=$((inbox_count + 1))
    fi
    
    # Waiting for something
    if [ -n "$waiting" ] && [ "$waiting" != "null" ] && [ "$waiting" != "initialization" ]; then
        echo "║  ⏳ WAITING: $waiting                                       ║"
        inbox_count=$((inbox_count + 1))
    fi
    
    if [ $inbox_count -eq 0 ]; then
        echo "║  ✅ No pending items                                       ║"
    fi
    
    echo "║                                                            ║"

    # BRICK HEALTH (if active brick)
    if [ "${brick:-none}" != "none" ] && [ -d "bricks/$brick" ]; then
        echo "╠══════════════════════════════════════════════════════════════╣"
        echo "║  🧱 BRICK HEALTH: $brick                                   ║"
        
        local brick_status locked locked_by
        brick_status="$(yaml_get_nested "bricks/$brick/brick.yaml" "brick" "status" 2>/dev/null)"
        locked="$(yaml_get_nested "bricks/$brick/brick.yaml" "brick" "locked" 2>/dev/null)"
        locked_by="$(yaml_get_nested "bricks/$brick/brick.yaml" "brick" "locked_by" 2>/dev/null)"
        
        echo "║  Status:    ${brick_status:-unknown}                        ║"
        [ "$locked" = "true" ] && echo "║  Locked:    by $locked_by                                   ║"
        
        # File counts per directory
        local backend_files frontend_files test_files doc_files
        backend_files=$(find "bricks/$brick/backend" -type f 2>/dev/null | wc -l | tr -d ' ')
        frontend_files=$(find "bricks/$brick/frontend" -type f 2>/dev/null | wc -l | tr -d ' ')
        test_files=$(find "bricks/$brick/tests" -type f 2>/dev/null | wc -l | tr -d ' ')
        doc_files=$(find "bricks/$brick/docs" -type f 2>/dev/null | wc -l | tr -d ' ')
        
        echo "║  Backend:   ${backend_files} files                                ║"
        echo "║  Frontend:  ${frontend_files} files                                ║"
        echo "║  Tests:     ${test_files} files                                ║"
        echo "║  Docs:      ${doc_files} files                                ║"
        echo "║                                                            ║"
    fi

    # GATE PROGRESS
    echo "╠══════════════════════════════════════════════════════════════╣"
    echo "║  📊 GATE PROGRESS                                          ║"
    
    local completed=0 total=12
    for ((g=0; g<gate; g++)); do
        completed=$((completed + 1))
    done
    local pct=$((completed * 100 / total))
    local bar=""
    for ((i=0; i<pct/10; i++)); do bar="${bar}█"; done
    for ((i=pct/10; i<10; i++)); do bar="${bar}░"; done
    echo "║  Gates:  ${bar} ${completed}/${total} (${pct}%)                     ║"
    echo "║                                                            ║"

    # RECENT ACTIVITY
    echo "╠══════════════════════════════════════════════════════════════╣"
    echo "║  📜 RECENT ACTIVITY                                        ║"
    
    audit_list 4 2>/dev/null | grep "  \[" | while read line; do
        printf "║ %-58s ║\n" "${line:0:58}"
    done
    
    echo "║                                                            ║"

    # NEXT ACTION
    echo "╠══════════════════════════════════════════════════════════════╣"
    echo "║                                                            ║"
    
    # Determine next action based on state
    if [ "$session_status" != "active" ]; then
        echo "║  ▶  ai session start                                       ║"
    elif [ "${role:-unassigned}" = "unassigned" ]; then
        echo "║  ▶  ai role set <role>                                     ║"
    elif [ "$blocked" = "true" ]; then
        echo "║  ▶  Resolve: ${blocked_reason}                              ║"
    elif [ "${brick:-none}" = "none" ] && [ "$gate" -ge 5 ]; then
        echo "║  ▶  ai brick create <name>                                 ║"
    elif [ "$gate" -lt 11 ]; then
        echo "║  ▶  Complete Gate $gate requirements                        ║"
        echo "║  ▶  ai gate verify && ai gate advance                      ║"
    else
        echo "║  ▶  ai release create                                      ║"
    fi
    
    echo "║  ▶  ai validate                                             ║"
    echo "║                                                            ║"
    echo "╠══════════════════════════════════════════════════════════════╣"
    echo "║  ai work │ gate │ brick │ workflow │ validate │ release     ║"
    echo "╚══════════════════════════════════════════════════════════════╝"
    echo ""

    # Show next action from workflow engine
    workflow_next 2>/dev/null | grep "NEXT:" | sed 's/▶️  //'
}

main() { work_main "$@"; }
