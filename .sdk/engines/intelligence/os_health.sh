#!/data/data/com.termux/files/usr/bin/bash

# OS STABILITY INDEX — Measures Engineering OS health

os_health_check() {
    local engines_total=0 engines_healthy=0
    local commands_total=0 plugins_total=0
    local errors=0 warnings=0
    
    echo ""
    echo "════════════════════════════════════════════"
    echo "  ENGINEERING OS — STABILITY INDEX"
    echo "════════════════════════════════════════════"
    echo ""
    
    # Count engines
    if [ -d ".sdk/engines" ]; then
        engines_total=$(ls -1d .sdk/engines/*/ 2>/dev/null | wc -l | tr -d ' ')
        for eng in .sdk/engines/*/; do
            [ -d "$eng" ] || continue
            local name; name="$(basename "$eng")"
            if [ -f "$eng/engine.sh" ] || [ -f "$eng"/*/engine.sh ]; then
                engines_healthy=$((engines_healthy + 1))
            else
                echo "  ⚠️  $name: missing engine.sh"
                warnings=$((warnings + 1))
            fi
        done
    fi
    
    # Count commands
    commands_total=$(grep -c ")" .sdk/commands/ai 2>/dev/null)
    
    # Count plugins
    plugins_total=$(ls -1 .sdk/commands/plugins/*.sh 2>/dev/null | wc -l | tr -d ' ')
    
    # State check
    echo "  Engines:   $engines_healthy / $engines_total healthy"
    echo "  Commands:  $commands_total registered"
    echo "  Plugins:   $plugins_total loaded"
    
    # Registry check
    if [ -f ".sdk/engines/REGISTRY.yaml" ]; then
        echo "  Registry:  ✅ Present"
    else
        echo "  Registry:  ❌ Missing"
        errors=$((errors + 1))
    fi
    
    # State health
    if [ -d ".kin/state" ] && [ -f ".kin/state/session.yaml" ]; then
        echo "  State:     ✅ Healthy"
    else
        echo "  State:     ⚠️  Needs initialization"
        warnings=$((warnings + 1))
    fi
    
    # Git health
    if git rev-parse --git-dir >/dev/null 2>&1; then
        echo "  Git:       ✅ Repository"
    else
        echo "  Git:       ❌ Not a repository"
        errors=$((errors + 1))
    fi
    
    # Stability index
    local index=100
    [ "$engines_healthy" -lt "$engines_total" ] && index=$((index - 15))
    [ "$errors" -gt 0 ] && index=$((index - 10 * errors))
    [ "$warnings" -gt 0 ] && index=$((index - 3 * warnings))
    [ "$index" -lt 0 ] && index=0
    
    echo ""
    echo "  ─────────────────────────────────"
    echo "  Errors:     $errors"
    echo "  Warnings:   $warnings"
    echo "  Stability:  ${index}%"
    echo ""
    
    if [ "$index" -ge 95 ]; then echo "  Status: ✅ Production Ready"
    elif [ "$index" -ge 80 ]; then echo "  Status: ⚠️ Stable"
    elif [ "$index" -ge 60 ]; then echo "  Status: ⚠️ Needs Attention"
    else echo "  Status: ❌ Requires Maintenance"; fi
    
    echo "════════════════════════════════════════════"
}

os_health_check
