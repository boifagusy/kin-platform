#!/data/data/com.termux/files/usr/bin/bash

# GOVERNANCE LEVELS — Authoritative command classification

LEVEL0_COMMANDS="help tutorial examples explain plugins version"
LEVEL1_COMMANDS="status doctor validate workflow audit event knowledge copy clip"
LEVEL2_COMMANDS="gate brick plan design architect project"
LEVEL3_COMMANDS="implement code develop build fix modify patch generate scaffold migrate test restore investigate certify intelligence discovery contract task"
LEVEL4_COMMANDS="release deploy publish rollback delete"

governance_level() {
    local cmd="$1"
    if echo " $LEVEL4_COMMANDS " | grep -q " $cmd "; then echo 4
    elif echo " $LEVEL3_COMMANDS " | grep -q " $cmd "; then echo 3
    elif echo " $LEVEL2_COMMANDS " | grep -q " $cmd "; then echo 2
    elif echo " $LEVEL1_COMMANDS " | grep -q " $cmd "; then echo 1
    elif echo " $LEVEL0_COMMANDS " | grep -q " $cmd "; then echo 0
    else echo 1; fi  # Default: Level 1 (light check)
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
