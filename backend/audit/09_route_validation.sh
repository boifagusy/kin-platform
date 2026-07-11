#!/data/data/com.termux/files/usr/bin/bash
source "$(dirname "$0")/framework.sh"

BACKEND_DIR="$1"
REPORT_DIR="$2"
TEMP_DIR="$3"
cd "$BACKEND_DIR"

REPORT="$REPORT_DIR/09_route_validation.md"

{
    echo "# Route Validation & Conflict Detection"
    echo ""
    
    echo "## Route Conflicts"
    echo '```'
    DETECT_ROUTE_CONFLICTS "$TEMP_DIR"
    echo '```'
    echo ""
    
    echo "## Controller Method Validation"
    echo '```'
    VALIDATE_CONTROLLER_METHODS "$TEMP_DIR"
    echo '```'
    echo ""
    
    echo "## Watchtower Route Status"
    echo '```'
    echo "Expected Watchtower endpoints:"
    php artisan route:list 2>&1 | grep -i "watchtower\|monitor\|health\|metric" || echo "  No Watchtower routes found in current route list"
    echo ""
    echo "Checking route files for Watchtower references:"
    for file in routes/*.php; do
        if grep -qi "watchtower\|monitor\|health\|metric" "$file" 2>/dev/null; then
            echo "  FOUND in $file:"
            grep -n "watchtower\|monitor\|health\|metric" "$file" | sed 's/^/    /'
        fi
    done
    echo '```'
    echo ""
    
    echo "## Missing Expected Endpoints"
    echo '```'
    # Check if standard Watchtower endpoints exist
    endpoints=(
        "watchtower/health"
        "watchtower/api"
        "watchtower/database"
        "watchtower/queue"
        "watchtower/performance"
        "watchtower/notifications"
        "watchtower/plugins"
        "watchtower/errors"
        "watchtower/security"
        "watchtower/system"
        "watchtower/metrics"
    )
    
    for endpoint in "${endpoints[@]}"; do
        if php artisan route:list 2>&1 | grep -q "$endpoint"; then
            echo "  ✓ $endpoint"
        else
            echo "  ✗ $endpoint - MISSING"
        fi
    done
    echo '```'
    
} > "$REPORT" 2>&1

echo "Phase 9 complete"
