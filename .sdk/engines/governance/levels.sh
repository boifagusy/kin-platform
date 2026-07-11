#!/data/data/com.termux/files/usr/bin/bash

# Execution Levels — Policy-based command classification

# Level 0 — Public (skip governance)
LEVEL0_COMMANDS="help tutorial examples explain version docs plugins"

# Level 1 — Read (light check: session + role)
LEVEL1_COMMANDS="status doctor validate workflow audit event knowledge"

# Level 2 — Planning (standard check: session + role + gate)
LEVEL2_COMMANDS="gate brick plan design architect"

# Level 3 — Modification (full check: all guards)
LEVEL3_COMMANDS="implement code develop build fix modify patch generate scaffold migrate test restore restore_run integrate merge"

# Level 4 — Critical (full check + confirmation + approval + clean git)
LEVEL4_COMMANDS="release deploy publish rollback delete"

# Get execution level for a command
governance_level() {
    local cmd="$1"
    if echo "$LEVEL4_COMMANDS" | grep -qw "$cmd"; then echo 4
    elif echo "$LEVEL3_COMMANDS" | grep -qw "$cmd"; then echo 3
    elif echo "$LEVEL2_COMMANDS" | grep -qw "$cmd"; then echo 2
    elif echo "$LEVEL1_COMMANDS" | grep -qw "$cmd"; then echo 1
    else echo 0
    fi
}

# Get level name
governance_level_name() {
    case "$1" in
        0) echo "PUBLIC — No checks" ;;
        1) echo "READ — Session + Role" ;;
        2) echo "PLANNING — Session + Role + Gate" ;;
        3) echo "MODIFICATION — Full Guard Suite" ;;
        4) echo "CRITICAL — Full + Approval + Git Clean" ;;
    esac
}
