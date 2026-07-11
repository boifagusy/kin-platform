#!/data/data/com.termux/files/usr/bin/bash
# KIN Backend Audit Script - Phase 1 Evidence Collection
# Run from ~/kin_project/backend
# Output: backend_audit_report.txt

REPORT="backend_audit_report.txt"
BACKUP_DIR="../backup"
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')

# Initialize report
{
echo "================================================"
echo "KIN BACKEND AUDIT REPORT"
echo "Generated: $TIMESTAMP"
echo "Working Directory: $(pwd)"
echo "================================================"
echo ""

# ============================================
# SYSTEM INFORMATION
# ============================================
echo "================================================"
echo "SECTION 1: SYSTEM INFORMATION"
echo "================================================"
echo ""
echo "--- PWD ---"
pwd
echo ""
echo "--- PHP VERSION ---"
php -v 2>&1
echo ""
echo "--- COMPOSER VERSION ---"
composer --version 2>&1
echo ""
echo "--- GIT BRANCH ---"
git branch 2>&1
echo ""
echo "--- GIT STATUS ---"
git status 2>&1
echo ""
echo "--- GIT LOG (last 5) ---"
git log --oneline -5 2>&1
echo ""

# ============================================
# CONTROLLERS INVENTORY
# ============================================
echo "================================================"
echo "SECTION 2: CONTROLLERS INVENTORY"
echo "================================================"
echo ""
echo "--- ALL CONTROLLERS (with size & modified date) ---"
find app/Http/Controllers -type f -name "*.php" -exec stat -c "%n | %s bytes | %y" {} \; 2>/dev/null | sort
echo ""
echo "--- CONTROLLER COUNT ---"
echo "Total controllers: $(find app/Http/Controllers -type f -name "*.php" | wc -l)"
echo ""

# ============================================
# WATCHTOWER CONTROLLERS - DEEP ANALYSIS
# ============================================
echo "================================================"
echo "SECTION 3: WATCHTOWER CONTROLLER ANALYSIS"
echo "================================================"
echo ""

# Find all Watchtower-related controllers
WATCHTOWER_CONTROLLERS=$(find app/Http/Controllers -type f -name "*.php" -exec grep -li "watchtower\|monitor\|metrics\|health" {} \; 2>/dev/null)

if [ -z "$WATCHTOWER_CONTROLLERS" ]; then
    echo "WARNING: No Watchtower controllers found via grep!"
    echo "Checking alternative names..."
    WATCHTOWER_CONTROLLERS=$(find app/Http/Controllers -type f -name "*Monitor*.php" -o -name "*Watchtower*.php" -o -name "*Metric*.php" -o -name "*Health*.php" 2>/dev/null)
fi

echo "Watchtower Controllers Found:"
echo "$WATCHTOWER_CONTROLLERS"
echo ""

# Detailed analysis of each Watchtower controller
for controller in $WATCHTOWER_CONTROLLERS; do
    if [ -f "$controller" ]; then
        echo "---"
        echo "CONTROLLER: $controller"
        echo "FILE SIZE: $(stat -c "%s" "$controller" 2>/dev/null || stat -f "%z" "$controller" 2>/dev/null) bytes"
        echo ""
        
        # Extract class name
        CLASSNAME=$(grep -E "^class [A-Za-z]+" "$controller" | head -1 | sed 's/class //' | sed 's/ .*//')
        echo "CLASS: $CLASSNAME"
        
        # Extract namespace
        NAMESPACE=$(grep "^namespace " "$controller" | sed 's/namespace //' | sed 's/;//')
        echo "NAMESPACE: $NAMESPACE"
        echo ""
        
        # List public methods
        echo "PUBLIC METHODS:"
        grep -E "public function [a-zA-Z_]+" "$controller" | sed 's/.*public function //' | sed 's/(.*//' | while read method; do
            echo "  - $method()"
        done
        echo ""
        
        # Check dependencies (use statements)
        echo "IMPORTS:"
        grep "^use " "$controller" | sed 's/^use //' | sed 's/;//' | sort -u | while read import; do
            echo "  - $import"
        done
        echo ""
    fi
done

# ============================================
# SERVICES INVENTORY
# ============================================
echo "================================================"
echo "SECTION 4: SERVICES INVENTORY"
echo "================================================"
echo ""
echo "--- ALL SERVICES ---"
find app/Services -type f -name "*.php" -exec stat -c "%n | %s bytes | %y" {} \; 2>/dev/null | sort
echo ""
echo "--- SERVICE COUNT ---"
echo "Total services: $(find app/Services -type f -name "*.php" 2>/dev/null | wc -l)"
echo ""

# Find Watchtower-related services
echo "--- WATCHTOWER-RELATED SERVICES ---"
find app/Services -type f -name "*.php" -exec grep -li "watchtower\|monitor\|metrics\|health\|queue\|performance" {} \; 2>/dev/null | sort
echo ""

# ============================================
# MODELS INVENTORY
# ============================================
echo "================================================"
echo "SECTION 5: MODELS INVENTORY"
echo "================================================"
echo ""
echo "--- ALL MODELS ---"
find app/Models -type f -name "*.php" -exec stat -c "%n | %s bytes | %y" {} \; 2>/dev/null | sort
echo ""
echo "--- MODEL COUNT ---"
echo "Total models: $(find app/Models -type f -name "*.php" 2>/dev/null | wc -l)"
echo ""

# ============================================
# MIGRATIONS INVENTORY
# ============================================
echo "================================================"
echo "SECTION 6: MIGRATIONS INVENTORY"
echo "================================================"
echo ""
echo "--- ALL MIGRATIONS ---"
find database/migrations -type f -name "*.php" | sort
echo ""
echo "--- MIGRATION COUNT ---"
echo "Total migrations: $(find database/migrations -type f -name "*.php" 2>/dev/null | wc -l)"
echo ""

echo "--- WATCHTOWER-RELATED MIGRATIONS ---"
find database/migrations -type f -name "*.php" -exec grep -li "watchtower\|monitor\|metrics\|health\|queue_monitoring\|api_monitoring\|performance" {} \; 2>/dev/null | sort
echo ""

# ============================================
# ROUTES
# ============================================
echo "================================================"
echo "SECTION 7: ROUTES"
echo "================================================"
echo ""
echo "--- ROUTE FILES ---"
for file in routes/*.php; do
    if [ -f "$file" ]; then
        echo "File: $file"
        echo "Lines: $(wc -l < "$file")"
        echo ""
    fi
done

echo "--- COMPLETE ROUTE LIST ---"
php artisan route:list 2>&1
echo ""

echo "--- ROUTE LIST (JSON) ---"
php artisan route:list --json 2>&1 > /tmp/route_list.json
if [ -f /tmp/route_list.json ]; then
    cat /tmp/route_list.json
else
    echo "ERROR: Could not generate JSON route list"
fi
echo ""

echo "--- WATCHTOWER ROUTES IN FILES ---"
for file in routes/*.php; do
    if [ -f "$file" ]; then
        echo "Checking $file for Watchtower references:"
        grep -n "watchtower\|monitor\|metrics\|health" "$file" 2>/dev/null || echo "  No Watchtower references found"
        echo ""
    fi
done

# ============================================
# CONFIG INVENTORY
# ============================================
echo "================================================"
echo "SECTION 8: CONFIG INVENTORY"
echo "================================================"
echo ""
echo "--- ALL CONFIG FILES ---"
find config -type f -name "*.php" | sort
echo ""

echo "--- WATCHTOWER REFERENCES IN CONFIG ---"
grep -r "watchtower\|monitor\|metrics\|health\|queue_monitor\|api_monitor" config/ 2>/dev/null
echo ""

# ============================================
# MIDDLEWARE INVENTORY
# ============================================
echo "================================================"
echo "SECTION 9: MIDDLEWARE INVENTORY"
echo "================================================"
echo ""
echo "--- ALL MIDDLEWARE ---"
find app/Http/Middleware -type f -name "*.php" | sort
echo ""

echo "--- AUTH MIDDLEWARE CHECK ---"
echo "Checking for 'auth:sanctum':"
grep -r "auth:sanctum\|auth:api\|Sanctum" app/Http/Middleware/ config/ 2>/dev/null
echo ""
echo "Checking for 'admin' middleware:"
find app/Http/Middleware -type f -exec grep -li "admin\|Administrator\|isAdmin" {} \; 2>/dev/null
echo ""
echo "Checking for 'role' middleware:"
find app/Http/Middleware -type f -exec grep -li "role\|hasRole" {} \; 2>/dev/null
echo ""
echo "Checking for 'permission' middleware:"
find app/Http/Middleware -type f -exec grep -li "permission\|can\|Gate" {} \; 2>/dev/null
echo ""

# ============================================
# POLICIES INVENTORY
# ============================================
echo "================================================"
echo "SECTION 10: POLICIES INVENTORY"
echo "================================================"
echo ""
if [ -d "app/Policies" ]; then
    find app/Policies -type f -name "*.php" | sort
    echo ""
    echo "Total policies: $(find app/Policies -type f -name "*.php" | wc -l)"
else
    echo "No Policies directory found"
fi
echo ""

# ============================================
# COMMANDS INVENTORY
# ============================================
echo "================================================"
echo "SECTION 11: ARTISAN COMMANDS"
echo "================================================"
echo ""
echo "--- CUSTOM COMMANDS ---"
if [ -d "app/Console/Commands" ]; then
    find app/Console/Commands -type f -name "*.php" | sort
    echo ""
    echo "Total commands: $(find app/Console/Commands -type f -name "*.php" | wc -l)"
else
    echo "No custom commands directory found"
fi
echo ""

echo "--- WATCHTOWER-RELATED COMMANDS ---"
find app/Console/Commands -type f -name "*.php" -exec grep -li "watchtower\|monitor\|metrics" {} \; 2>/dev/null | sort
echo ""

# ============================================
# JOBS INVENTORY
# ============================================
echo "================================================"
echo "SECTION 12: JOBS INVENTORY"
echo "================================================"
echo ""
if [ -d "app/Jobs" ]; then
    find app/Jobs -type f -name "*.php" | sort
    echo ""
    echo "Total jobs: $(find app/Jobs -type f -name "*.php" | wc -l)"
else
    echo "No Jobs directory found"
fi
echo ""

# ============================================
# EVENTS INVENTORY
# ============================================
echo "================================================"
echo "SECTION 13: EVENTS INVENTORY"
echo "================================================"
echo ""
if [ -d "app/Events" ]; then
    find app/Events -type f -name "*.php" | sort
    echo ""
    echo "Total events: $(find app/Events -type f -name "*.php" | wc -l)"
else
    echo "No Events directory found"
fi
echo ""

# ============================================
# LISTENERS INVENTORY
# ============================================
echo "================================================"
echo "SECTION 14: LISTENERS INVENTORY"
echo "================================================"
echo ""
if [ -d "app/Listeners" ]; then
    find app/Listeners -type f -name "*.php" | sort
    echo ""
    echo "Total listeners: $(find app/Listeners -type f -name "*.php" | wc -l)"
else
    echo "No Listeners directory found"
fi
echo ""

# ============================================
# SCHEDULED TASKS
# ============================================
echo "================================================"
echo "SECTION 15: SCHEDULED TASKS"
echo "================================================"
echo ""
php artisan schedule:list 2>&1
echo ""

# ============================================
# QUEUE STATUS
# ============================================
echo "================================================"
echo "SECTION 16: QUEUE STATUS"
echo "================================================"
echo ""
echo "--- FAILED JOBS ---"
php artisan queue:failed 2>&1
echo ""

# ============================================
# ENVIRONMENT CHECK
# ============================================
echo "================================================"
echo "SECTION 17: ENVIRONMENT VARIABLES"
echo "================================================"
echo ""
echo "Checking for Watchtower-related env vars:"
if [ -f ".env" ]; then
    grep -i "WATCHTOWER\|HEALTH\|MONITOR\|METRICS" .env 2>/dev/null | sed 's/=.*/=***HIDDEN***/' || echo "  None found"
else
    echo "  WARNING: .env file not found in current directory"
fi
echo ""
echo "APP_ENV: $(grep APP_ENV .env 2>/dev/null | sed 's/.*=//' || echo 'NOT SET')"
echo "DB_CONNECTION: $(grep DB_CONNECTION .env 2>/dev/null | sed 's/.*=//' || echo 'NOT SET')"
echo "QUEUE_CONNECTION: $(grep QUEUE_CONNECTION .env 2>/dev/null | sed 's/.*=//' || echo 'NOT SET')"
echo "CACHE_DRIVER: $(grep CACHE_DRIVER .env 2>/dev/null | sed 's/.*=//' || echo 'NOT SET')"

# ============================================
# BACKUP INVENTORY
# ============================================
echo "================================================"
echo "SECTION 18: BACKUP INVENTORY"
echo "================================================"
echo ""
if [ -d "$BACKUP_DIR" ]; then
    echo "--- BACKUP DIRECTORY STRUCTURE ---"
    find "$BACKUP_DIR" -maxdepth 2 -type d 2>/dev/null | sort
    echo ""
    echo "--- BACKUP CONTENTS ---"
    ls -la "$BACKUP_DIR" 2>/dev/null
    echo ""
    echo "--- GIT INFO IN BACKUP ---"
    if [ -d "$BACKUP_DIR/.git" ]; then
        cd "$BACKUP_DIR"
        echo "Git log (last 5):"
        git log --oneline -5 2>&1
        cd - > /dev/null
    fi
    echo ""
    echo "--- BACKUP TIMESTAMPS ---"
    find "$BACKUP_DIR" -maxdepth 1 -type f -exec stat -c "%n | %y" {} \; 2>/dev/null
else
    echo "WARNING: Backup directory not found at $BACKUP_DIR"
fi
echo ""

# ============================================
# BACKUP COMPARISON
# ============================================
echo "================================================"
echo "SECTION 19: BACKUP COMPARISON"
echo "================================================"
echo ""

if [ -d "$BACKUP_DIR" ]; then
    # Compare controllers
    echo "--- CONTROLLERS IN BACKUP BUT NOT IN CURRENT ---"
    if [ -d "$BACKUP_DIR/app/Http/Controllers" ]; then
        for file in $(find "$BACKUP_DIR/app/Http/Controllers" -type f -name "*.php" 2>/dev/null); do
            relative_path=${file#$BACKUP_DIR/}
            if [ ! -f "$relative_path" ]; then
                echo "MISSING: $relative_path"
            fi
        done
    fi
    echo ""
    
    # Compare routes
    echo "--- ROUTE FILES IN BACKUP ---"
    if [ -d "$BACKUP_DIR/routes" ]; then
        find "$BACKUP_DIR/routes" -type f -name "*.php" 2>/dev/null | sort
    fi
    echo ""
    
    # Compare services
    echo "--- SERVICES IN BACKUP BUT NOT IN CURRENT ---"
    if [ -d "$BACKUP_DIR/app/Services" ]; then
        for file in $(find "$BACKUP_DIR/app/Services" -type f -name "*.php" 2>/dev/null); do
            relative_path=${file#$BACKUP_DIR/}
            if [ ! -f "$relative_path" ]; then
                echo "MISSING: $relative_path"
            fi
        done
    fi
    echo ""
fi

# ============================================
# RUNTIME VALIDATION
# ============================================
echo "================================================"
echo "SECTION 20: RUNTIME VALIDATION"
echo "================================================"
echo ""

# Create temporary PHP script for instantiation tests
cat > /tmp/test_controllers.php << 'PHPEOF'
<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$controllers = glob('app/Http/Controllers/*Monitor*.php');
$controllers = array_merge($controllers, glob('app/Http/Controllers/*Watchtower*.php'));
$controllers = array_merge($controllers, glob('app/Http/Controllers/*Health*.php'));
$controllers = array_merge($controllers, glob('app/Http/Controllers/*Metric*.php'));

foreach ($controllers as $controllerFile) {
    // Extract namespace and class
    $content = file_get_contents($controllerFile);
    preg_match('/namespace (.*?);/', $content, $namespaceMatch);
    preg_match('/class (\w+)/', $content, $classMatch);
    
    if (isset($namespaceMatch[1]) && isset($classMatch[1])) {
        $fullClass = $namespaceMatch[1] . '\\' . $classMatch[1];
        echo "Testing: $fullClass\n";
        try {
            $instance = new $fullClass();
            echo "  ✓ PASS\n";
        } catch (\Throwable $e) {
            echo "  ✗ FAIL: " . $e->getMessage() . "\n";
        }
    }
}
PHPEOF

php /tmp/test_controllers.php 2>&1
rm -f /tmp/test_controllers.php
echo ""

# ============================================
# DEPENDENCY MATRIX
# ============================================
echo "================================================"
echo "SECTION 21: DEPENDENCY MATRIX"
echo "================================================"
echo ""

echo "Controller | Exists | Service | Model | Migration | Middleware | Route | Runtime"
echo "-----------|--------|---------|-------|-----------|------------|-------|--------"

for controller in $WATCHTOWER_CONTROLLERS; do
    if [ -f "$controller" ]; then
        basename=$(basename "$controller" .php)
        exists="YES"
        
        # Check service dependency
        service="NONE"
        if grep -q "use.*Service" "$controller" 2>/dev/null; then
            service_class=$(grep "use.*Service" "$controller" | head -1 | awk '{print $NF}' | sed 's/;//')
            if [ -f "app/Services/${service_class}.php" ]; then
                service="FOUND"
            else
                service="MISSING"
            fi
        fi
        
        # Check model dependency
        model="NONE"
        if grep -q "use App\\Models" "$controller" 2>/dev/null; then
            model_class=$(grep "use App\\Models" "$controller" | head -1 | awk '{print $NF}' | sed 's/;//')
            if [ -f "app/Models/${model_class}.php" ]; then
                model="FOUND"
            else
                model="MISSING"
            fi
        fi
        
        # Check migration
        migration="NONE"
        
        # Check middleware reference
        middleware="NONE"
        if grep -q "middleware" "$controller" 2>/dev/null; then
            middleware="REFERENCED"
        fi
        
        # Check route
        route="NONE"
        controller_name=$(echo "$basename" | sed 's/Controller//')
        if grep -ri "$controller_name" routes/ 2>/dev/null; then
            route="FOUND"
        else
            route="MISSING"
        fi
        
        # Runtime check
        runtime="NOT TESTED"
        
        echo "$basename | $exists | $service | $model | $migration | $middleware | $route | $runtime"
    fi
done
echo ""

# ============================================
# RECOVERY CLASSIFICATION
# ============================================
echo "================================================"
echo "SECTION 22: RECOVERY CLASSIFICATION"
echo "================================================"
echo ""

echo "--- SAFE (can restore immediately) ---"
echo "Controllers with all dependencies verified:"
# Will be populated after analysis
echo "NONE YET - Requires human review of above data"
echo ""

echo "--- PARTIAL (need investigation) ---"
echo "Controllers with some dependencies:"
echo "NONE YET - Requires human review of above data"
echo ""

echo "--- BLOCKED (missing critical dependencies) ---"
echo "Controllers that cannot be restored:"
echo "NONE YET - Requires human review of above data"
echo ""

} > "$REPORT" 2>&1

echo "Audit Complete"
echo "Report: $(pwd)/$REPORT"
