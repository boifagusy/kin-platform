#!/data/data/com.termux/files/usr/bin/bash

work_main() {
    source "$SDK_ROOT/engines/gate/engine.sh" 2>/dev/null
    source "$SDK_ROOT/kernel/state.sh" 2>/dev/null
    source "$SDK_ROOT/engines/git/engine.sh" 2>/dev/null
    
    local project sdk_ver gate gate_name role brick branch changes duration
    project="$(get_project_root 2>/dev/null | xargs basename 2>/dev/null)"
    sdk_ver="$(grep "version:" "$SDK_ROOT/sdk.yaml" 2>/dev/null | head -1 | sed 's/.*: //')"
    gate="$(gate_current 2>/dev/null)"
    gate_name="$(gate_name "$gate" 2>/dev/null)"
    role="$(state_read "ai.yaml" "active_role" 2>/dev/null | tr -d ' ')"
    brick="$(state_read "brick.yaml" "active_brick" 2>/dev/null | tr -d ' ')"
    branch="$(git_branch 2>/dev/null)"
    changes=$(git status --porcelain 2>/dev/null | wc -l | tr -d ' ')
    
    # Session duration
    local started; started="$(state_read "session.yaml" "started" 2>/dev/null | tr -d ' ')"
    if [ -n "$started" ] && [ "$started" != "unknown" ]; then
        local min=$(( ($(date +%s) - $(date -d "${started}" +%s 2>/dev/null || echo 0)) / 60 ))
        [ "$min" -lt 60 ] && duration="${min}m" || duration="$((min/60))h $((min%60))m"
    else
        duration="--"
    fi
    
    local greeting; local h=$(date +%H)
    [ "$h" -lt 12 ] && greeting="Morning" || [ "$h" -lt 17 ] && greeting="Afternoon" || greeting="Evening"
    
    clear 2>/dev/null || true
    
    cat << EOF
╔══════════════════════════════════════════╗
║   ENGINEERING OS — ${project}                 ║
╠══════════════════════════════════════════╣
║  Good ${greeting}, Idowu.                    ║
║                                          ║
║  SDK: v${sdk_ver}  |  Git: ${branch}  |  ${duration}       ║
║  Gate ${gate}: ${gate_name}                     ║
║  Role: ${role:-unassigned}  |  Brick: ${brick:-none}              ║
╠══════════════════════════════════════════╣
║  📋 $([ "${role:-unassigned}" = "unassigned" ] && echo "Role not set — ai role set architect" || echo "All clear — ai gate advance")     ║
╠══════════════════════════════════════════╣
║  ai work │ help │ gate │ brick │ validate  ║
╚══════════════════════════════════════════╝
EOF
    echo ""
}

main() { work_main "$@"; }
