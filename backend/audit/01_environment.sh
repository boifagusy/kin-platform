#!/data/data/com.termux/files/usr/bin/bash
source "$(dirname "$0")/framework.sh"

BACKEND_DIR="$1"
REPORT_DIR="$2"
TEMP_DIR="$3"
cd "$BACKEND_DIR"

REPORT="$REPORT_DIR/01_environment.md"

{
    echo "# Environment Audit"
    echo ""
    echo "## System"
    echo '```'
    echo "Platform: $(uname -a)"
    echo "Termux: $(echo $TERMUX_VERSION 2>/dev/null || echo 'N/A')"
    echo "PWD: $(pwd)"
    echo '```'
    echo ""
    
    echo "## PHP"
    echo '```'
    php -v 2>&1
    echo '```'
    echo ""
    
    echo "## Laravel"
    echo '```'
    php artisan about 2>&1
    echo '```'
    echo ""
    
    echo "## Composer"
    echo '```'
    composer validate 2>&1
    echo ""
    echo "Monitoring packages:"
    composer show 2>/dev/null | grep -i "monitor\|watchtower\|horizon\|telescope\|health\|metric" || echo "  None found"
    echo '```'
    echo ""
    
    echo "## Cache Status"
    echo '```'
    echo "Routes cached: $([ -f bootstrap/cache/routes-v7.php ] && echo 'YES ⚠ (may contain stale routes)' || echo 'NO')"
    echo "Config cached: $([ -f bootstrap/cache/config.php ] && echo 'YES' || echo 'NO')"
    echo "Events cached: $([ -f bootstrap/cache/events.php ] && echo 'YES' || echo 'NO')"
    echo '```'
    echo ""
    
    echo "## Service Providers"
    echo '```'
    if [ -f "bootstrap/providers.php" ]; then
        grep "::class" bootstrap/providers.php
    elif [ -f "config/app.php" ]; then
        grep -A 50 "'providers'" config/app.php | grep "App\\\\\|::class" | head -30
    fi
    echo '```'
    echo ""
    
    echo "## Package Discovery"
    echo '```'
    if [ -f "composer.json" ]; then
        php -r '
        $json = json_decode(file_get_contents("composer.json"), true);
        $providers = $json["extra"]["laravel"]["providers"] ?? [];
        if (empty($providers)) {
            echo "No auto-discovered providers\n";
        } else {
            echo "Auto-discovered providers:\n";
            foreach ($providers as $p) echo "  - $p\n";
        }
        '
    fi
    echo '```'
    
} > "$REPORT" 2>&1

echo "Phase 1 complete"
