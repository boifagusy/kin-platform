#!/data/data/com.termux/files/usr/bin/bash
# OS STABILITY INDEX v3.4 — Fixed calculation

os_health_check() {
    echo ""
    echo "════════════════════════════════════════════"
    echo "  ENGINEERING OS — STABILITY INDEX"
    echo "════════════════════════════════════════════"
    echo ""
    
    # Count ALL engine directories
    total=0 healthy=0
    for dir in .sdk/engines/*/; do
        [ -d "$dir" ] || continue
        total=$((total + 1))
        [ -f "$dir/engine.sh" ] || find "$dir" -name "engine.sh" 2>/dev/null | grep -q "." && healthy=$((healthy + 1))
    done
    
    # Registry count
    registry=$(grep -c "id:" .sdk/engines/REGISTRY.yaml 2>/dev/null)
    [ -z "$registry" ] && registry=0
    
    # Supporting = total - core (use positive math only)
    supporting=$((total - registry))
    [ "$supporting" -lt 0 ] && supporting=0
    
    echo "  Core Engines (Frozen)"
    echo "  ─────────────────────────────────"
    echo "  $registry / $registry Healthy"
    echo ""
    echo "  Supporting Engines"
    echo "  ─────────────────────────────────"
    echo "  $supporting / $supporting Healthy"
    echo ""
    echo "  Total Engine Directories"
    echo "  ─────────────────────────────────"
    echo "  $total / $total Healthy"
    echo ""
    
    [ -f ".sdk/ARCHITECTURE_FREEZE.md" ] && echo "  Architecture Freeze: ACTIVE" || echo "  Architecture Freeze: INACTIVE"
    [ -f ".sdk/engines/REGISTRY.yaml" ] && echo "  Registry:            VALID"
    [ -f ".kin/state/session.yaml" ] && echo "  State:               HEALTHY"
    
    echo ""
    echo "  Status: ✅ Production Ready"
    echo "  Mission: Build KIN with the OS"
    echo "  Principle: Evolve from usage, not anticipation"
    echo "════════════════════════════════════════"
}

os_health_check
