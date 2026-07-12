#!/data/data/com.termux/files/usr/bin/bash

# OS STABILITY INDEX v2.0 — Reads authoritative registry

os_health_check() {
    local registry=".sdk/engines/REGISTRY.yaml"
    local expected=0 healthy=0 missing=0 unexpected=0
    
    echo ""
    echo "════════════════════════════════════════════"
    echo "  ENGINEERING OS — STABILITY INDEX"
    echo "════════════════════════════════════════════"
    echo ""
    
    # Read expected engines from registry
    if [ -f "$registry" ]; then
        expected=$(grep -c "id:" "$registry" 2>/dev/null)
        
        while IFS= read -r line; do
            local id; id=$(echo "$line" | sed 's/.*id: //')
            [ -z "$id" ] && continue
            
            if [ -d ".sdk/engines/$id" ] || [ -f ".sdk/engines/$id/engine.sh" ]; then
                healthy=$((healthy + 1))
            else
                echo "  ❌ Missing: $id"
                missing=$((missing + 1))
            fi
        done < <(grep "id:" "$registry" 2>/dev/null)
    fi
    
    # Find unexpected engines
    for dir in .sdk/engines/*/; do
        [ -d "$dir" ] || continue
        local name; name="$(basename "$dir")"
        if ! grep -q "id: $name" "$registry" 2>/dev/null; then
            [ "$name" != "REGISTRY.yaml" ] && unexpected=$((unexpected + 1))
        fi
    done
    
    echo "  REGISTRY"
    echo "  ─────────────────────────────────"
    echo "  Expected:   $expected"
    echo "  Healthy:    $healthy"
    echo "  Missing:    $missing"
    echo "  Unexpected: $unexpected"
    
    # State & Git
    echo ""
    [ -f ".kin/state/session.yaml" ] && echo "  State:      ✅ Healthy" || echo "  State:      ⚠️ Not initialized"
    [ -f ".sdk/ARCHITECTURE_FREEZE.md" ] && echo "  Freeze:     ✅ Active" || echo "  Freeze:     ⚠️ Not active"
    git rev-parse --git-dir >/dev/null 2>&1 && echo "  Git:        ✅ Repository" || echo "  Git:        ❌ Not a repo"
    
    # Score
    local index=100
    [ "$missing" -gt 0 ] && index=$((index - 20 * missing))
    [ "$unexpected" -gt 0 ] && index=$((index - 5 * unexpected))
    [ "$index" -lt 0 ] && index=0
    
    echo ""
    echo "  ─────────────────────────────────"
    echo "  Stability:  ${index}%"
    echo "  Warnings:   $((missing + unexpected))"
    echo ""
    [ "$index" -ge 95 ] && echo "  Status: ✅ Production Ready"
    [ "$index" -ge 80 ] && [ "$index" -lt 95 ] && echo "  Status: ⚠️ Needs Attention"
    [ "$index" -lt 80 ] && echo "  Status: ❌ Maintenance Required"
    echo "════════════════════════════════════════"
}

os_health_check
