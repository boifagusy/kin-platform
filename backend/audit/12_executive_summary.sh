#!/data/data/com.termux/files/usr/bin/bash
source "$(dirname "$0")/framework.sh"

BACKEND_DIR="$1"
REPORT_DIR="$2"
TEMP_DIR="$3"
cd "$BACKEND_DIR"

REPORT="$REPORT_DIR/12_executive_summary.md"

{
    echo "# Executive Summary"
    echo ""
    echo "## Current Architecture Status"
    echo ""
    
    # Backend health
    echo "### Backend"
    if php artisan --version >/dev/null 2>&1; then
        echo "✓ Healthy - Laravel $(php artisan --version 2>&1 | grep -oP '\d+\.\d+\.\d+')"
    else
        echo "⚠ Issues detected"
    fi
    echo ""
    
    # Database
    echo "### Database"
    if php artisan migrate:status >/dev/null 2>&1; then
        echo "✓ Migrations accessible"
    else
        echo "⚠ Migration check failed"
    fi
    echo ""
    
    # Frontend
    echo "### Frontend"
    if [ -d "../frontend" ]; then
        if [ -f "../frontend/package.json" ]; then
            echo "✓ Frontend directory present"
            echo "  Package manager: $([ -f '../frontend/package-lock.json' ] && echo 'npm' || echo 'unknown')"
            if [ -f "../frontend/node_modules/.vite/deps/_metadata.json" ]; then
                echo "  Build: Vite cache present"
            fi
        fi
    else
        echo "✗ Frontend not found"
    fi
    echo ""
    
    # Watchtower status
    echo "### Watchtower Status"
    watchtower_controllers=$(find app/Http/Controllers -type f \( -name "*Watchtower*" -o -name "*Monitor*" -o -name "*Metric*" -o -name "*Health*" \) 2>/dev/null | wc -l)
    watchtower_routes=$(php artisan route:list 2>&1 | grep -ci "watchtower\|monitor\|metric\|health" || echo "0")
    watchtower_services=$(find app/Services -type f -name "*Monitor*" -o -name "*Metric*" -o -name "*Health*" 2>/dev/null | wc -l)
    
    echo "- Controllers: $watchtower_controllers"
    echo "- Routes: $watchtower_routes"
    echo "- Services: $watchtower_services"
    echo ""
    
    if [ "$watchtower_controllers" -gt 0 ] && [ "$watchtower_routes" -eq 0 ]; then
        echo "⚠ STATUS: Watchtower controllers exist but routes are missing"
        echo "  Recovery: Routes need to be restored from backup or recreated"
    elif [ "$watchtower_controllers" -gt 0 ] && [ "$watchtower_routes" -gt 0 ]; then
        echo "✓ STATUS: Watchtower partially operational"
    elif [ "$watchtower_controllers" -eq 0 ]; then
        echo "⚠ STATUS: Watchtower controllers missing - may need full recovery"
    fi
    echo ""
    
    echo "## Missing Files Summary"
    echo '```'
    echo "Controllers without routes:"
    for controller in $(find app/Http/Controllers -type f \( -name "*Watchtower*" -o -name "*Monitor*" -o -name "*Metric*" -o -name "*Health*" \) 2>/dev/null); do
        class=$(GET_CLASS "$controller")
        if ! php artisan route:list 2>&1 | grep -q "$class"; then
            echo "  - $(GET_NAMESPACE "$controller")\\$class (no route)"
        fi
    done
    echo '```'
    echo ""
    
    echo "## Recovery Risk Assessment"
    echo ""
    
    # Calculate risk
    risk="LOW"
    if [ "$watchtower_controllers" -gt 5 ]; then
        risk="MEDIUM"
    fi
    if [ ! -d "../backup" ]; then
        risk="HIGH - No backup directory found"
    fi
    if ! git status >/dev/null 2>&1; then
        risk="HIGH - Git not available"
    fi
    
    echo "Risk Level: **$risk**"
    echo ""
    
    echo "## Recommended Next Steps"
    echo ""
    echo "1. Review full audit reports in this directory"
    echo "2. Verify backup integrity before any recovery"
    echo "3. Start with SAFE route restoration only"
    echo "4. Test KIN Safety Platform functionality after each batch"
    echo "5. Do NOT restore entire backup - use evidence-based recovery"
    echo ""
    echo "---"
    echo "Audit completed: $(date)"
    
} > "$REPORT" 2>&1

echo "Phase 12 complete"
