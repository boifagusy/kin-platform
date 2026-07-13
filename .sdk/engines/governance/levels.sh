#!/data/data/com.termux/files/usr/bin/bash

# GOVERNANCE LEVELS — Authoritative command classification
# Level 0: No governance (bootstrap commands to break deadlocks)
LEVEL0_COMMANDS="help tutorial examples explain plugins version role session work status copy clip"

# Level 1: Light check (session + role)
LEVEL1_COMMANDS="doctor validate workflow audit event knowledge discovery"

# Level 2: Planning check (session + role + gate)
LEVEL2_COMMANDS="gate brick plan design architect project investigate contract"

# Level 3: Full check (all guards)
LEVEL3_COMMANDS="implement code develop build fix modify patch generate scaffold migrate test restore certify intelligence task"

# Level 4: Critical check (full + approval + git clean)
LEVEL4_COMMANDS="release deploy publish rollback delete"

governance_level() {
    local cmd="$1"
    if echo " $LEVEL4_COMMANDS " | grep -q " $cmd "; then echo 4
    elif echo " $LEVEL3_COMMANDS " | grep -q " $cmd "; then echo 3
    elif echo " $LEVEL2_COMMANDS " | grep -q " $cmd "; then echo 2
    elif echo " $LEVEL1_COMMANDS " | grep -q " $cmd "; then echo 1
    elif echo " $LEVEL0_COMMANDS " | grep -q " $cmd "; then echo 0
    else echo 1; fi
}

governance_level_name() {
    case "$1" in
        0) echo "PUBLIC" ;;
        1) echo "READ" ;;
        2) echo "PLANNING" ;;
        3) echo "MODIFICATION" ;;
        4) echo "CRITICAL" ;;
    esac
}
