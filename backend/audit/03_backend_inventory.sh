#!/data/data/com.termux/files/usr/bin/bash
source "$(dirname "$0")/framework.sh"

BACKEND_DIR="$1"
REPORT_DIR="$2"
TEMP_DIR="$3"
cd "$BACKEND_DIR"

REPORT="$REPORT_DIR/03_backend_inventory.md"

{
    echo "# Backend Inventory"
    echo ""
    
    echo "## Watchtower Controllers"
    echo '```'
    for file in $(find app/Http/Controllers -type f \( -name "*Watchtower*" -o -name "*Monitor*" -o -name "*Metric*" -o -name "*Health*" -o -name "*Observability*" \) 2>/dev/null); do
        echo "FILE: $file"
        FILE_INFO "$file"
        echo "CLASS: $(GET_NAMESPACE "$file")\\$(GET_CLASS "$file")"
        echo "METHODS:"
        GET_METHODS "$file" | sed 's/^/  - /'
        echo "DEPENDENCIES:"
        grep "^use " "$file" | sed 's/^use //' | sed 's/;//' | sed 's/^/  - /'
        echo ""
    done
    echo '```'
    echo ""
    
    echo "## KIN Safety Controllers (Gen 1)"
    echo '```'
    find app/Http/Controllers -type f -name "*.php" | grep -vi "watchtower\|monitor\|metric\|health\|observability" | while read file; do
        echo "$(GET_NAMESPACE "$file")\\$(GET_CLASS "$file")"
    done | sort
    echo '```'
    echo ""
    
    echo "## Services"
    echo '```'
    find app/Services -type f -name "*.php" 2>/dev/null | while read file; do
        echo "SERVICE: $(GET_NAMESPACE "$file")\\$(GET_CLASS "$file")"
        echo "METHODS:"
        GET_METHODS "$file" | sed 's/^/  - /'
        echo ""
    done
    echo '```'
    echo ""
    
    echo "## Models"
    echo '```'
    find app/Models -type f -name "*.php" 2>/dev/null | while read file; do
        class=$(GET_CLASS "$file")
        table=$(grep "protected \$table" "$file" 2>/dev/null | sed "s/.*= '//" | sed "s/'.*//")
        echo "$class → Table: ${table:-'(default)'}"
    done | sort
    echo '```'
    echo ""
    
    echo "## Middleware"
    echo '```'
    find app/Http/Middleware -type f -name "*.php" 2>/dev/null | while read file; do
        echo "$(GET_CLASS "$file")"
    done | sort
    echo ""
    if [ -f "app/Http/Kernel.php" ]; then
        echo "Registered middleware aliases:"
        grep -A 30 "routeMiddleware" app/Http/Kernel.php | grep "'" | head -20
    fi
    echo '```'
    echo ""
    
    echo "## Migrations"
    echo '```'
    echo "Watchtower-related:"
    find database/migrations -type f -name "*.php" -exec grep -l "watchtower\|monitor\|metrics\|health\|queue_monitoring\|performance_metrics" {} \; 2>/dev/null | sort
    echo ""
    echo "All migrations:"
    find database/migrations -type f -name "*.php" | sort | while read f; do echo "  $(basename $f)"; done
    echo '```'
    
} > "$REPORT" 2>&1

echo "Phase 3 complete"
