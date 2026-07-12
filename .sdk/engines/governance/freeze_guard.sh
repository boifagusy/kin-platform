#!/data/data/com.termux/files/usr/bin/bash

# ARCHITECTURE FREEZE GUARD
# Blocks creation of new engines without ADR approval

freeze_check_new_engine() {
    local engine_name="$1"
    
    # Check if architecture freeze is active
    if [ ! -f ".sdk/ARCHITECTURE_FREEZE.md" ]; then
        return 0  # No freeze active
    fi
    
    # Check if ADR exists for this engine
    local adr_approved=false
    for adr in docs/adr/ADR-*.md; do
        [ -f "$adr" ] || continue
        if grep -q "$engine_name" "$adr" 2>/dev/null && grep -q "Status.*Approved" "$adr" 2>/dev/null; then
            adr_approved=true
            break
        fi
    done
    
    if ! $adr_approved; then
        echo ""
        echo "═══════════════════════════════════════"
        echo "  ARCHITECTURE FREEZE — BLOCKED"
        echo "═══════════════════════════════════════"
        echo "  New engine: $engine_name"
        echo "  Status: ❌ BLOCKED"
        echo ""
        echo "  Reason: Architecture Freeze is active."
        echo "  New core engines require an ADR."
        echo ""
        echo "  Required:"
        echo "    1. Create ADR: docs/adr/ADR-NNNN.md"
        echo "    2. Get Engineering Manager approval"
        echo "    3. Update REGISTRY.yaml"
        echo ""
        return 1
    fi
    
    return 0
}

# Run check if called with engine name
[ -n "$1" ] && freeze_check_new_engine "$1"
