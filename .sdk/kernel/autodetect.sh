#!/data/data/com.termux/files/usr/bin/bash
# Auto-detect active brick from current directory

autodetect_brick() {
    local dir=$(pwd)
    local brick=""
    
    case "$dir" in
        *safety*|*Safety*) brick="safety_score" ;;
        *watchtower*|*Watchtower*) brick="watchtower" ;;
        *sos*|*SOS*) brick="sos" ;;
        *auth*) brick="authentication" ;;
        *checkin*|*CheckIn*) brick="checkin" ;;
        *dashboard*) brick="dashboard" ;;
        *settings*) brick="settings" ;;
        *profile*) brick="profile" ;;
        *notification*) brick="notifications" ;;
    esac
    
    if [ -n "$brick" ]; then
        source .sdk/kernel/state.sh 2>/dev/null
        state_write "brick.yaml" "active_brick" "$brick" 2>/dev/null
        mkdir -p "bricks/$brick"
    fi
}
