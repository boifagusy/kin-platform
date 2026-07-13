#!/data/data/com.termux/files/usr/bin/bash

# INVESTIGATION ENGINE
# No implementation begins before investigation

if [ -n "$SDK_ROOT" ]; then
    KERNEL_DIR="$SDK_ROOT/kernel"
    ENGINES_DIR="$SDK_ROOT/engines"
else
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    ENGINES_DIR="$(dirname "$SCRIPT_DIR")"
    KERNEL_DIR="$(dirname "$ENGINES_DIR")/kernel"
fi

source "$KERNEL_DIR/state.sh" 2>/dev/null
source "$KERNEL_DIR/yaml.sh" 2>/dev/null
source "$ENGINES_DIR/gate/engine.sh" 2>/dev/null

INVEST_DIR=".kin/investigations"

# ── Investigate a target ──
investigate() {
    local target="${1:-services}"
    local now; now="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
    local report="$INVEST_DIR/${target}_$(date +%Y%m%d_%H%M%S).yaml"
    mkdir -p "$INVEST_DIR"
    
    echo ""
    echo "═══════════════════════════════════════"
    echo "  INVESTIGATION: $target"
    echo "═══════════════════════════════════════"
    echo ""
    
    local findings=() gaps=() risks=()
    
    case "$target" in
        services|backend)
            echo "  Scanning backend services..."
            if [ -d "backend/app/Services" ]; then
                local count; count=$(find backend/app/Services -name "*.php" -type f 2>/dev/null | wc -l | tr -d ' ')
                echo "  Services found: $count"
                
                # Check for missing contracts
                for svc in $(find backend/app/Services -name "*.php" -type f 2>/dev/null); do
                    [ -f "$svc" ] || continue
                    local name; name="$(basename "$svc" .php)"
                    if [ ! -f ".sdk/contracts/watchtower/${name}.yaml" ] && [ ! -f ".sdk/contracts/*/${name}.yaml" ]; then
                        gaps+=("No contract for: $name")
                    fi
                done
                
                findings+=("$count service files exist")
                [ ${#gaps[@]} -gt 0 ] && risks+=("${#gaps[@]} services without contracts")
            else
                gaps+=("No Services directory found")
            fi
            ;;
            
        controllers)
            echo "  Scanning controllers..."
            if [ -d "backend/app/Http/Controllers" ]; then
                local count; count=$(find backend/app/Http/Controllers -name "*.php" -type f 2>/dev/null | wc -l | tr -d ' ')
                findings+=("$count controllers found")
            fi
            ;;
            
        routes)
            echo "  Scanning routes..."
            if [ -f "backend/routes/web.php" ]; then
                local count; count=$(grep -c "Route::" backend/routes/web.php 2>/dev/null)
                findings+=("$count web routes defined")
            fi
            if [ -f "backend/routes/api.php" ]; then
                local count; count=$(grep -c "Route::" backend/routes/api.php 2>/dev/null)
                findings+=("$count API routes defined")
            fi
            ;;
            
        *)
            echo "  Generic investigation..."
            findings+=("Target: $target")
            ;;
    esac
    
    # Save report
    cat > "$report" << YAML
investigation:
  target: $target
  timestamp: $now
  project: $(get_project_root 2>/dev/null | xargs basename 2>/dev/null)
  gate: $(gate_current 2>/dev/null)
  
findings:
$(for f in "${findings[@]}"; do echo "  - $f"; done)

gaps:
$(for g in "${gaps[@]}"; do echo "  - $g"; done)

risks:
$(for r in "${risks[@]}"; do echo "  - $r"; done)

recommendation: $([ ${#gaps[@]} -eq 0 ] && echo "READY" || echo "NEEDS_ATTENTION")
approval_required: $([ ${#risks[@]} -gt 0 ] && echo "true" || echo "false")
YAML
    
    echo ""
    echo "  Findings:  ${#findings[@]}"
    echo "  Gaps:      ${#gaps[@]}"
    echo "  Risks:     ${#risks[@]}"
    echo "  Report:    $report"
    echo ""
    
    if [ ${#gaps[@]} -gt 0 ]; then
        echo "  ⚠️  Gaps detected — approval required before implementation"
        return 1
    else
        echo "  ✅ Ready for implementation"
        return 0
    fi
}

# ── List investigations ──
investigate_list() {
    echo "INVESTIGATION REPORTS"
    echo "═══════════════════════════════════════"
    if [ -d "$INVEST_DIR" ] && [ -n "$(ls -A "$INVEST_DIR" 2>/dev/null)" ]; then
        for f in "$INVEST_DIR"/*.yaml; do
            [ -f "$f" ] || continue
            local target ts recommendation
            target="$(grep "target:" "$f" 2>/dev/null | head -1 | sed 's/.*: //')"
            ts="$(grep "timestamp:" "$f" 2>/dev/null | head -1 | sed 's/.*: //')"
            recommendation="$(grep "recommendation:" "$f" 2>/dev/null | sed 's/.*: //')"
            echo "  $target — $recommendation ($ts)"
        done
    else
        echo "  (no investigations yet)"
    fi
}

# Dispatch
case "${1:-list}" in
    services|backend|controllers|routes|models) investigate "$1" ;;
    list)   investigate_list ;;
    *)
        echo "Usage: ai investigate [services|controllers|routes|models|list]"
        echo ""
        echo "  ai investigate services    Scan backend services"
        echo "  ai investigate controllers Scan controllers"
        echo "  ai investigate routes      Analyze routes"
        echo "  ai investigate list        View all reports"
        ;;
esac
