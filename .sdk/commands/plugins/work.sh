# Description: Engineering Command Center v4.0 — Production Ready
# Requires: state gate workflow validate git brick knowledge audit

work_main() {
    source "$SDK_ROOT/engines/gate/engine.sh" 2>/dev/null
    source "$SDK_ROOT/engines/workflow/engine.sh" 2>/dev/null
    source "$SDK_ROOT/engines/validate/engine.sh" 2>/dev/null
    source "$SDK_ROOT/engines/git/engine.sh" 2>/dev/null
    source "$SDK_ROOT/engines/brick/engine.sh" 2>/dev/null
    source "$SDK_ROOT/engines/audit/engine.sh" 2>/dev/null
    source "$SDK_ROOT/kernel/state.sh" 2>/dev/null
    source "$SDK_ROOT/kernel/yaml.sh" 2>/dev/null
    
    # ── State ──
    local project_name sdk_version gate gate_name gate_status gate_purpose
    local brick brick_status brick_locked locked_by
    local role session_status waiting blocked blocked_reason
    local branch changes hour greeting
    local session_started session_duration
    
    project_name="$(basename "$(get_project_root 2>/dev/null)")"
    sdk_version="$(grep "version:" "$SDK_ROOT/sdk.yaml" 2>/dev/null | head -1 | sed 's/.*: //')"
    gate="$(gate_current 2>/dev/null)"
    gate_name="$(gate_name "$gate" 2>/dev/null)"
    gate_status="$(gate_status 2>/dev/null)"
    gate_purpose="$(gate_description "$gate" 2>/dev/null)"
    brick="$(state_read "brick.yaml" "active_brick" 2>/dev/null | tr -d ' ')"
    role="$(state_read "ai.yaml" "active_role" 2>/dev/null | tr -d ' ')"
    session_status="$(state_read "session.yaml" "status" 2>/dev/null | tr -d ' ')"
    waiting="$(state_read "ai.yaml" "waiting_for" 2>/dev/null | tr -d ' ')"
    blocked="$(state_read "gate.yaml" "blocked" 2>/dev/null | tr -d ' ')"
    blocked_reason="$(state_read "gate.yaml" "blocked_reason" 2>/dev/null | tr -d ' ')"
    branch="$(git_branch 2>/dev/null)"
    changes=$(git status --porcelain 2>/dev/null | wc -l | tr -d ' ')
    session_started="$(state_read "session.yaml" "started" 2>/dev/null | tr -d ' ')"
    
    # Session duration
    if [ -n "$session_started" ] && [ "$session_started" != "unknown" ]; then
        local started_epoch now_epoch diff_min
        started_epoch=$(date -d "${session_started}" +%s 2>/dev/null || echo 0)
        now_epoch=$(date +%s)
        diff_min=$(( (now_epoch - started_epoch) / 60 ))
        session_duration="${diff_min} min"
    else
        session_duration="--"
    fi
    
    hour=$(date +%H)
    if [ "$hour" -lt 12 ]; then greeting="Good Morning"
    elif [ "$hour" -lt 17 ]; then greeting="Good Afternoon"
    else greeting="Good Evening"
    fi
    
    # ── Brick details ──
    local bf=0 ff=0 tf=0 df=0
    if [ "${brick:-none}" != "none" ] && [ -f "bricks/$brick/brick.yaml" ]; then
        brick_status="$(yaml_get_nested "bricks/$brick/brick.yaml" "brick" "status" 2>/dev/null)"
        brick_locked="$(yaml_get_nested "bricks/$brick/brick.yaml" "brick" "locked" 2>/dev/null)"
        locked_by="$(yaml_get_nested "bricks/$brick/brick.yaml" "brick" "locked_by" 2>/dev/null)"
        bf=$(find "bricks/$brick/backend" -type f 2>/dev/null | wc -l | tr -d ' ')
        ff=$(find "bricks/$brick/frontend" -type f 2>/dev/null | wc -l | tr -d ' ')
        tf=$(find "bricks/$brick/tests" -type f 2>/dev/null | wc -l | tr -d ' ')
        df=$(find "bricks/$brick/docs" -type f 2>/dev/null | wc -l | tr -d ' ')
    fi
    
    # ── Brick count ──
    local total_bricks=0
    if [ -d "bricks" ]; then
        total_bricks=$(ls -1d bricks/*/ 2>/dev/null | wc -l | tr -d ' ')
    fi
    
    # ── Health ──
    local k_ok="✅" w_ok="✅" g_ok="✅" b_ok="✅" v_ok="✅" git_ok="✅"
    [ -f "$SDK_ROOT/kernel/common.sh" ] || k_ok="❌"
    [ -f "$SDK_ROOT/engines/workflow/engine.sh" ] || w_ok="❌"
    [ -f "$SDK_ROOT/engines/gate/engine.sh" ] || g_ok="❌"
    [ -f "$SDK_ROOT/engines/brick/engine.sh" ] || b_ok="❌"
    [ -f "$SDK_ROOT/engines/validate/engine.sh" ] || v_ok="❌"
    git rev-parse --git-dir >/dev/null 2>&1 || git_ok="❌"
    
    # ── Inbox ──
    local inbox=0
    [ "$blocked" = "true" ] && inbox=$((inbox + 1))
    [ "$gate" = "3" ] || [ "$gate" = "11" ] && inbox=$((inbox + 1))
    [ "${role:-unassigned}" = "unassigned" ] && inbox=$((inbox + 1))
    
    # ── Active locks ──
    local lock_count=0 lock_info=""
    if [ -d "bricks" ]; then
        for bd in bricks/*/; do
            [ -f "$bd/brick.yaml" ] || continue
            local lk lkb
            lk=$(yaml_get_nested "$bd/brick.yaml" "brick" "locked" 2>/dev/null)
            if [ "$lk" = "true" ]; then
                lkb=$(yaml_get_nested "$bd/brick.yaml" "brick" "locked_by" 2>/dev/null)
                lock_info="${lock_info}  $(basename "$bd") → ${lkb}\n"
                lock_count=$((lock_count + 1))
            fi
        done
    fi
    
    # ── Footer status ──
    local footer="Engineering OS Ready"
    [ "$blocked" = "true" ] && footer="⛔ ${blocked_reason}"
    [ "${role:-unassigned}" = "unassigned" ] && footer="Awaiting role assignment"
    [ "${brick:-none}" != "none" ] && [ "$gate" -ge 6 ] && footer="${brick} — Gate ${gate} in Progress"
    
    clear 2>/dev/null || true
    
    # ═══════════════ HEADER ═══════════════
    cat << HEADER
╔══════════════════════════════════════════════════════════════╗
║              ENGINEERING OS — COMMAND CENTER                 ║
╠══════════════════════════════════════════════════════════════╣
║  ${greeting}, Engineering Manager.                              ║
║                                                            ║
║  Project:   ${project_name}     SDK: v${sdk_version}     Health: All ${k_ok}  ║
║  Git:       ${branch} (${changes} files)     Session: ${session_duration}         ║
║                                                            ║
║  CURRENT GATE: ${gate} — ${gate_name}                              ║
║  Status:     ${gate_status}     Purpose: ${gate_purpose}                  ║
║                                                            ║
║  Manager: Idowu              AI Role: ${role:-unassigned}                 ║
║  Brick:    ${brick:-none}              Bricks: ${total_bricks} total              ║
║                                                            ║
HEADER

    # ═══════════════ INBOX ═══════════════
    if [ $inbox -gt 0 ]; then
        echo "╠══════════════════════════════════════════════════════════════╣"
        echo "║  📋 INBOX (${inbox})                                                ║"
        [ "$blocked" = "true" ] && echo "║  ⛔ BLOCKED: ${blocked_reason}                                  ║"
        [ "$gate" = "3" ] || [ "$gate" = "11" ] && echo "║  ✋ Gate ${gate} requires Engineering Manager approval             ║"
        [ "${role:-unassigned}" = "unassigned" ] && echo "║  👤 Assign AI role to proceed                                 ║"
        echo "║                                                            ║"
    fi

    # ═══════════════ ACTIVE LOCKS ═══════════════
    echo "╠══════════════════════════════════════════════════════════════╣"
    if [ $lock_count -gt 0 ]; then
        echo "║  🔒 ACTIVE LOCKS (${lock_count})                                         ║"
        echo -e "$lock_info" | while read line; do
            [ -n "$line" ] && printf "║ %-58s ║\n" "$line"
        done
    else
        echo "║  🔒 ACTIVE LOCKS: None                                      ║"
    fi
    echo "║                                                            ║"

    # ═══════════════ BRICK HEALTH ═══════════════
    if [ "${brick:-none}" != "none" ] && [ -d "bricks/$brick" ]; then
        echo "╠══════════════════════════════════════════════════════════════╣"
        echo "║  🧱 BRICK: ${brick}                                            ║"
        echo "║  Status: ${brick_status:-unknown}    BE: ${bf} files  FE: ${ff} files  Tests: ${tf}  Docs: ${df}  ║"
        [ "$brick_locked" = "true" ] && echo "║  🔒 Locked by: ${locked_by}                                      ║"
        echo "║                                                            ║"
    fi

    # ═══════════════ VALIDATION ═══════════════
    echo "╠══════════════════════════════════════════════════════════════╣"
    echo "║  ✔ VALIDATION                                               ║"
    echo "║  Kernel: ${k_ok}   Workflow: ${w_ok}   Gate: ${g_ok}   Brick: ${b_ok}   Validate: ${v_ok}   Git: ${git_ok}    ║"
    echo "║                                                            ║"

    # ═══════════════ WORKFLOW ═══════════════
    echo "╠══════════════════════════════════════════════════════════════╣"
    echo "║  🔄 WORKFLOW                                               ║"
    echo "║  ← Gate ${gate} — ${gate_name} →                               ║"
    if [ "$gate" -lt 11 ]; then
        echo "║  Next: Gate $((gate + 1)) — $(gate_name $((gate + 1)) 2>/dev/null)                                     ║"
    fi
    echo "║  Target: Gate 11 — Release                                  ║"
    echo "║                                                            ║"

    # ═══════════════ PROJECT PROGRESS ═══════════════
    local gate_pct=$((gate * 100 / 12))
    echo "╠══════════════════════════════════════════════════════════════╣"
    echo "║  📦 PROJECT PROGRESS                                       ║"
    echo "║  Gates:   ${gate}/12 (${gate_pct}%)     Bricks: ${total_bricks} total              ║"
    echo "║  Release: $(git describe --tags --abbrev=0 2>/dev/null || echo 'none')                                      ║"
    echo "║                                                            ║"

    # ═══════════════ NEXT STEP ═══════════════
    echo "╠══════════════════════════════════════════════════════════════╣"
    echo "║  ▶ NEXT STEP                                               ║"
    
    if [ "$session_status" != "active" ]; then
        echo "║  Start a session: ai session start                          ║"
    elif [ "${role:-unassigned}" = "unassigned" ]; then
        echo "║  Assign AI role: ai role set architect                      ║"
    elif [ "$blocked" = "true" ]; then
        echo "║  Resolve blocker: ${blocked_reason}                          ║"
    elif [ "$gate" -eq 0 ]; then
        echo "║  Verify environment: ai doctor && ai gate verify            ║"
    elif [ "$gate" -ge 5 ] && [ "${brick:-none}" = "none" ]; then
        echo "║  Select brick: ai brick create <name>                       ║"
    elif [ "$gate" -lt 11 ]; then
        echo "║  Complete Gate ${gate} then: ai gate verify && ai gate advance  ║"
    else
        echo "║  Create release: ai release create                          ║"
    fi
    
    echo "║                                                            ║"
    echo "╚══════════════════════════════════════════════════════════════╝"
    echo "  ${footer}"
    echo ""
}

main() { work_main "$@"; }
