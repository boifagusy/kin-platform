#!/data/data/com.termux/files/usr/bin/bash

# ENGINEERING COMMAND CENTER — Professional Edition
# Real-time, accurate, no hardcoded paths

work_main() {
    source "$SDK_ROOT/engines/gate/engine.sh" 2>/dev/null
    source "$SDK_ROOT/engines/workflow/engine.sh" 2>/dev/null
    source "$SDK_ROOT/kernel/state.sh" 2>/dev/null
    source "$SDK_ROOT/kernel/yaml.sh" 2>/dev/null
    source "$SDK_ROOT/engines/git/engine.sh" 2>/dev/null
    
    # ── Live State (no cached values) ──
    local project sdk_ver gate gate_name gate_status gate_purpose
    local brick role session_status session_started
    local branch changes
    
    project="$(get_project_root 2>/dev/null | xargs basename 2>/dev/null)"
    sdk_ver="$(grep "version:" "$SDK_ROOT/sdk.yaml" 2>/dev/null | head -1 | sed 's/.*: //')"
    gate="$(gate_current 2>/dev/null)"
    gate_name="$(gate_name "$gate" 2>/dev/null)"
    gate_status="$(state_read "gate.yaml" "status" 2>/dev/null | tr -d ' ')"
    gate_purpose="$(gate_description "$gate" 2>/dev/null)"
    brick="$(state_read "brick.yaml" "active_brick" 2>/dev/null | tr -d ' ')"
    role="$(state_read "ai.yaml" "active_role" 2>/dev/null | tr -d ' ')"
    session_status="$(state_read "session.yaml" "status" 2>/dev/null | tr -d ' ')"
    session_started="$(state_read "session.yaml" "started" 2>/dev/null | tr -d ' ')"
    branch="$(git_branch 2>/dev/null)"
    changes=$(git status --porcelain 2>/dev/null | wc -l | tr -d ' ')
    
    # ── Session duration ──
    local duration="--"
    if [ -n "$session_started" ] && [ "$session_started" != "unknown" ]; then
        local started_epoch now_epoch diff_min
        started_epoch=$(date -d "${session_started}" +%s 2>/dev/null || echo 0)
        now_epoch=$(date +%s)
        diff_min=$(( (now_epoch - started_epoch) / 60 ))
        if [ "$diff_min" -lt 60 ]; then
            duration="${diff_min}m"
        else
            local hours=$((diff_min / 60))
            local mins=$((diff_min % 60))
            duration="${hours}h ${mins}m"
        fi
    fi
    
    # ── Greeting ──
    local hour greeting
    hour=$(date +%H)
    if [ "$hour" -lt 12 ]; then greeting="Good Morning"
    elif [ "$hour" -lt 17 ]; then greeting="Good Afternoon"
    else greeting="Good Evening"
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
    local inbox=0 inbox_items=""
    [ "$blocked" = "true" ] && { inbox=$((inbox + 1)); inbox_items="${inbox_items}\n║  ⛔ Gate blocked"; }
    [ "${role:-unassigned}" = "unassigned" ] && { inbox=$((inbox + 1)); inbox_items="${inbox_items}\n║  👤 Role not assigned — ai role set architect"; }
    [ "${brick:-none}" = "none" ] && [ "$gate" -ge 5 ] && { inbox=$((inbox + 1)); inbox_items="${inbox_items}\n║  🧱 No active brick"; }
    
    clear 2>/dev/null || true
    
    # ── RENDER ──
    cat << HEADER
╔══════════════════════════════════════════════════════════════╗
║              ENGINEERING OS — COMMAND CENTER                 ║
╠══════════════════════════════════════════════════════════════╣
║  ${greeting}, Engineering Manager.                              ║
║                                                            ║
║  Project:   ${project}     SDK: v${sdk_ver}     Health: All ${k_ok}  ║
║  Git:       ${branch} (${changes} files)     Session: ${duration}         ║
║                                                            ║
║  GATE ${gate} — ${gate_name}                                 ║
║  Status:   ${gate_status}     Purpose: ${gate_purpose}                  ║
║                                                            ║
║  Manager:  Idowu              AI Role: ${role:-unassigned}                 ║
║  Brick:    ${brick:-none}              Bricks: $(ls -1d bricks/*/ 2>/dev/null | wc -l | tr -d ' ') total              ║
║                                                            ║
HEADER

    # ── INBOX ──
    if [ $inbox -gt 0 ]; then
        echo "╠══════════════════════════════════════════════════════════════╣"
        echo "║  📋 INBOX (${inbox})                                                ║"
        echo -e "$inbox_items"
        echo "║                                                            ║"
    fi

    # ── FOOTER ──
    cat << FOOTER
╠══════════════════════════════════════════════════════════════╣
║  ✔ Kernel: ${k_ok}  Workflow: ${w_ok}  Gate: ${g_ok}  Brick: ${b_ok}  Validate: ${v_ok}  Git: ${git_ok}   ║
╠══════════════════════════════════════════════════════════════╣
║  ▶ NEXT: $([ "${role:-unassigned}" = "unassigned" ] && echo "ai role set architect" || echo "ai gate advance")                                   ║
╚══════════════════════════════════════════════════════════════╝
FOOTER

    echo "  $(date '+%H:%M') — Engineering OS v${sdk_ver} — Ready"
    echo ""
}

main() { work_main "$@"; }
