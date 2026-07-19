#!/data/data/com.termux/files/usr/bin/bash
# KIN Backend Comprehensive Audit - Phase 1
# Termux-optimized with Git recovery, dependency chains, and frontend impact
# Version 2.0

set -e

REPORT="backend_audit_report.txt"
BACKUP_DIR="../backup"
FRONTEND_DIR="../frontend"
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
PWD=$(pwd)

# Detect platform for stat compatibility
if stat -c "%s" /dev/null 2>/dev/null; then
    STAT_CMD="stat -c"
else
    STAT_CMD="stat -f"
fi

echo "Starting KIN Backend Audit v2..."
echo "Platform: $(uname -a)"

# Initialize report
{
echo "================================================"
echo "KIN BACKEND COMPREHENSIVE AUDIT REPORT v2"
echo "Generated: $TIMESTAMP"
echo "Working Directory: $PWD"
echo "================================================"
echo ""

# ============================================
# SECTION 1: SYSTEM & ENVIRONMENT
# ============================================
echo "================================================"
echo "SECTION 1: SYSTEM & ENVIRONMENT"
echo "================================================"
echo ""

echo "--- TERMUX ENVIRONMENT ---"
echo "PWD: $PWD"
echo "ANDROID_ROOT: $ANDROID_ROOT"
echo "TERMUX_VERSION: $(termux-info 2>/dev/null || echo 'N/A')"
echo ""

echo "--- PHP ---"
php -v 2>&1 || echo "PHP not found!"
echo ""

echo "--- COMPOSER ---"
composer --version 2>&1 || echo "Composer not found!"
echo ""

echo "--- NODE (if available) ---"
node --version 2>&1 || echo "Node not found in path"
echo ""

echo "--- LARAVEL ---"
php artisan --version 2>&1 || echo "Artisan not responding!"
echo ""

echo "--- COMPOSER VALIDATION ---"
composer validate 2>&1 || echo "Composer validation failed"
echo ""

echo "--- COMPOSER PACKAGES (Watchtower related) ---"
composer show 2>/dev/null | grep -i "monitor\|watchtower\|metrics\|health\|horizon\|telescope" || echo "No Watchtower-related packages"
echo ""

echo "--- ENVIRONMENT VARIABLES (sanitized) ---"
if [ -f ".env" ]; then
    echo "APP_ENV: $(grep '^APP_ENV=' .env | cut -d= -f2)"
    echo "APP_DEBUG: $(grep '^APP_DEBUG=' .env | cut -d= -f2)"
    echo "DB_CONNECTION: $(grep '^DB_CONNECTION=' .env | cut -d= -f2)"
    echo "QUEUE_CONNECTION: $(grep '^QUEUE_CONNECTION=' .env | cut -d= -f2)"
    echo "CACHE_DRIVER: $(grep '^CACHE_DRIVER=' .env | cut -d= -f2)"
    echo "SESSION_DRIVER: $(grep '^SESSION_DRIVER=' .env | cut -d= -f2)"
    echo "BROADCAST_DRIVER: $(grep '^BROADCAST_DRIVER=' .env | cut -d= -f2)"
    echo ""
    echo "Watchtower/Health env vars:"
    grep -i "WATCHTOWER\|HEALTH\|MONITOR\|METRICS\|QUEUE_MONITOR" .env | sed 's/=.*/=***HIDDEN***/' || echo "  None found"
    echo ""
    echo "Sanctum/Auth env vars:"
    grep -i "SANCTUM\|JWT\|OAUTH\|PASSPORT" .env | sed 's/=.*/=***HIDDEN***/' || echo "  None found"
fi
echo ""

echo "--- LARAVEL ABOUT ---"
php artisan about 2>&1
echo ""

# ============================================
# SECTION 2: GIT RECOVERY ANALYSIS
# ============================================
echo "================================================"
echo "SECTION 2: GIT RECOVERY ANALYSIS"
echo "================================================"
echo ""

echo "--- CURRENT BRANCH ---"
git branch --show-current 2>&1
echo ""

echo "--- ALL BRANCHES (local) ---"
git branch 2>&1
echo ""

echo "--- ALL BRANCHES (remote) ---"
git branch -r 2>&1
echo ""

echo "--- ALL BRANCHES (including remote tracking) ---"
git branch -a 2>&1
echo ""

echo "--- TAGS ---"
git tag 2>&1
echo ""

echo "--- REFLOG (last 50 entries) ---"
git reflog -50 2>&1
echo ""

echo "--- STASH LIST ---"
git stash list 2>&1
echo ""

echo "--- RECENT COMMITS (last 20, graph) ---"
git log --all --graph --decorate --oneline -20 2>&1
echo ""

echo "--- COMMITS RELATED TO WATCHTOWER ---"
git log --all --oneline --grep="watchtower\|monitor\|metrics\|health\|observability" -i -20 2>&1 || echo "  No Watchtower commits found"
echo ""

echo "--- DELETED FILES (git log --diff-filter=D) ---"
git log --diff-filter=D --summary --oneline -20 2>&1 | grep -E "delete|Watchtower|Monitor|Metric|Health" || echo "  No relevant deleted files found"
echo ""

echo "--- RENAMED FILES ---"
git log --diff-filter=R --summary --oneline -20 2>&1 | grep -E "rename|Watchtower|Monitor" || echo "  No relevant renames found"
echo ""

echo "--- DANGLING COMMITS ---"
git fsck --lost-found 2>&1 | head -50 || echo "  No dangling objects or git fsck failed"
echo ""

echo "--- GIT STATUS ---"
git status 2>&1
echo ""

echo "--- GIT DIFF SUMMARY (working tree vs HEAD) ---"
git diff --name-status HEAD 2>&1
echo ""

echo "--- UNTRACKED FILES ---"
git ls-files --others --exclude-standard 2>&1
echo ""

# ============================================
# SECTION 3: COMPLETE CONTROLLER INVENTORY
# ============================================
echo "================================================"
echo "SECTION 3: COMPLETE CONTROLLER INVENTORY"
echo "================================================"
echo ""

echo "--- CONTROLLER DIRECTORY STRUCTURE ---"
find app/Http/Controllers -type d | sort
echo ""

echo "--- ALL CONTROLLERS ---"
find app/Http/Controllers -type f -name "*.php" | while read file; do
    size=$(ls -lh "$file" | awk '{print $5}')
    modified=$(ls -l "$file" | awk '{print $6, $7, $8}')
    echo "[$size] [$modified] $file"
done | sort
echo ""

echo "--- CONTROLLER CLASSES & NAMESPACES ---"
find app/Http/Controllers -type f -name "*.php" | while read file; do
    class=$(grep -E "^class [A-Z]" "$file" | head -1 | sed 's/class //' | sed 's/ .*//')
    namespace=$(grep "^namespace " "$file" | sed 's/namespace //' | sed 's/;//')
    echo "FILE: $file"
    echo "  NAMESPACE: $namespace"
    echo "  CLASS: $class"
    echo ""
done

# ============================================
# SECTION 4: WATCHTOWER CONTROLLER DEEP ANALYSIS
# ============================================
echo "================================================"
echo "SECTION 4: WATCHTOWER CONTROLLER ANALYSIS"
echo "================================================"
echo ""

# Find all potential Watchtower controllers
echo "--- WATCHTOWER CONTROLLERS FOUND ---"
WATCHTOWER_CONTROLLERS=$(find app/Http/Controllers -type f \( -name "*Watchtower*" -o -name "*Monitor*" -o -name "*Metric*" -o -name "*Health*" -o -name "*Observability*" \) 2>/dev/null)

if [ -z "$WATCHTOWER_CONTROLLERS" ]; then
    echo "No controllers with Watchtower names found directly."
    echo "Searching file contents..."
    WATCHTOWER_CONTROLLERS=$(find app/Http/Controllers -type f -name "*.php" -exec grep -li "watchtower\|monitoring\|queue_monitor\|api_monitor\|system.health\|performance.metric" {} \;)
fi

echo "$WATCHTOWER_CONTROLLERS"
echo ""

# Detailed method extraction
for controller in $WATCHTOWER_CONTROLLERS; do
    if [ -f "$controller" ]; then
        echo "=== CONTROLLER: $controller ==="
        
        # Extract methods with their visibility
        echo "METHODS:"
        grep -E "^\s*(public|protected|private) function" "$controller" | while read line; do
            visibility=$(echo "$line" | awk '{print $1}')
            method=$(echo "$line" | sed 's/.*function //' | sed 's/(.*//')
            echo "  [$visibility] $method()"
        done
        echo ""
        
        # Extract use statements (dependencies)
        echo "DEPENDENCIES:"
        grep "^use " "$controller" | sed 's/^use //' | sed 's/;//' | sort -u
        echo ""
        
        # Check constructor dependencies
        echo "CONSTRUCTOR INJECTION:"
        if grep -A 20 "__construct" "$controller" | grep -v "__construct" | grep -q '\$'; then
            grep -A 20 "__construct" "$controller" | grep '\$' | head -10
        else
            echo "  No constructor or no injected dependencies"
        fi
        echo ""
    fi
done

# ============================================
# SECTION 5: SERVICES INVENTORY
# ============================================
echo "================================================"
echo "SECTION 5: SERVICES INVENTORY"
echo "================================================"
echo ""

echo "--- ALL SERVICES ---"
find app/Services -type f -name "*.php" 2>/dev/null | while read file; do
    size=$(ls -lh "$file" | awk '{print $5}')
    echo "[$size] $file"
done | sort
echo ""

echo "--- WATCHTOWER-RELATED SERVICES ---"
find app/Services -type f -name "*.php" -exec grep -li "watchtower\|monitor\|metrics\|health\|queue\|performance\|plugin" {} \; 2>/dev/null | while read file; do
    echo "FILE: $file"
    grep -E "class [A-Z]" "$file" | head -1
    echo ""
done

# ============================================
# SECTION 6: MODELS INVENTORY
# ============================================
echo "================================================"
echo "SECTION 6: MODELS INVENTORY"
echo "================================================"
echo ""

echo "--- ALL MODELS ---"
find app/Models -type f -name "*.php" 2>/dev/null | while read file; do
    echo "$file"
done | sort
echo ""

echo "--- WATCHTOWER-RELATED MODELS ---"
find app/Models -type f -name "*.php" -exec grep -li "watchtower\|monitor\|metric\|queue\|api_call\|system_health\|performance" {} \; 2>/dev/null | sort
echo ""

# ============================================
# SECTION 7: MIGRATIONS
# ============================================
echo "================================================"
echo "SECTION 7: MIGRATIONS"
echo "================================================"
echo ""

echo "--- ALL MIGRATIONS ---"
find database/migrations -type f -name "*.php" | sort
echo ""

echo "--- WATCHTOWER MIGRATIONS ---"
find database/migrations -type f -name "*.php" -exec grep -l "watchtower\|monitor\|metrics\|health\|queue\|performance" {} \; 2>/dev/null | sort
echo ""

echo "--- MIGRATION STATUS ---"
php artisan migrate:status 2>&1 || echo "Migration check failed"
echo ""

# ============================================
# SECTION 8: ROUTES COMPLETE ANALYSIS
# ============================================
echo "================================================"
echo "SECTION 8: ROUTES"
echo "================================================"
echo ""

echo "--- ROUTE FILES ---"
for file in routes/*.php; do
    if [ -f "$file" ]; then
        echo "File: $file"
        echo "Size: $(ls -lh "$file" | awk '{print $5}')"
        echo "Lines: $(wc -l < "$file")"
        echo "Last modified: $(ls -l "$file" | awk '{print $6, $7, $8}')"
        echo ""
    fi
done

echo "--- CURRENT ROUTE LIST ---"
php artisan route:list 2>&1
echo ""

echo "--- ROUTE LIST JSON ---"
php artisan route:list --json 2>/dev/null > /tmp/route_list.json
if [ -f /tmp/route_list.json ]; then
    echo "JSON route list generated: $(wc -l < /tmp/route_list.json) lines"
    # Extract Watchtower-related routes
    grep -i "watchtower\|monitor\|health\|metric" /tmp/route_list.json 2>/dev/null || echo "  No Watchtower routes in JSON"
else
    echo "ERROR: Could not generate JSON routes"
fi
echo ""

echo "--- WATCHTOWER ROUTES IN FILES ---"
for file in routes/*.php; do
    if [ -f "$file" ]; then
        echo "File: $file"
        grep -n "watchtower\|monitor\|metrics\|health\|queue-monitor\|api-monitor" "$file" 2>/dev/null | head -30 || echo "  No Watchtower routes found"
        echo ""
    fi
done

echo "--- ALL ROUTE PREFIXES ---"
php artisan route:list 2>&1 | awk '{print $3}' | grep -E "^/" | sort -u
echo ""

# ============================================
# SECTION 9: CONFIG INVENTORY
# ============================================
echo "================================================"
echo "SECTION 9: CONFIG INVENTORY"
echo "================================================"
echo ""

echo "--- ALL CONFIG FILES ---"
find config -type f -name "*.php" | sort
echo ""

echo "--- WATCHTOWER CONFIG REFERENCES ---"
grep -r "watchtower\|monitor\|metrics\|health\|queue_monitoring" config/ 2>/dev/null | while read line; do
    echo "  $line"
done
echo ""

echo "--- CONFIG FILES WITH WATCHTOWER NAME ---"
find config -type f \( -name "*watchtower*" -o -name "*monitor*" -o -name "*health*" \) 2>/dev/null
echo ""

# ============================================
# SECTION 10: MIDDLEWARE INVENTORY
# ============================================
echo "================================================"
echo "SECTION 10: MIDDLEWARE INVENTORY"
echo "================================================"
echo ""

echo "--- ALL MIDDLEWARE ---"
find app/Http/Middleware -type f -name "*.php" | sort
echo ""

echo "--- MIDDLEWARE CLASSES ---"
find app/Http/Middleware -type f -name "*.php" | while read file; do
    class=$(grep -E "^class [A-Z]" "$file" | head -1 | awk '{print $2}')
    echo "  $class"
done
echo ""

echo "--- KERNEL REGISTERED MIDDLEWARE ---"
if [ -f "app/Http/Kernel.php" ]; then
    echo "Middleware Groups:"
    grep -A 50 "protected \$middlewareGroups" app/Http/Kernel.php | head -60
    echo ""
    echo "Route Middleware:"
    grep -A 30 "protected \$routeMiddleware" app/Http/Kernel.php | head -40
fi
echo ""

echo "--- AUTH GUARDS ---"
grep -A 10 "'guards'" config/auth.php 2>/dev/null | head -15
echo ""

# ============================================
# SECTION 11: POLICIES & GATES
# ============================================
echo "================================================"
echo "SECTION 11: POLICIES & AUTHORIZATION"
echo "================================================"
echo ""

echo "--- POLICIES ---"
find app/Policies -type f -name "*.php" 2>/dev/null | sort
echo ""

echo "--- GATES IN AuthServiceProvider ---"
if [ -f "app/Providers/AuthServiceProvider.php" ]; then
    grep -A 50 "boot()" app/Providers/AuthServiceProvider.php | head -60
fi
echo ""

# ============================================
# SECTION 12: COMMANDS & JOBS
# ============================================
echo "================================================"
echo "SECTION 12: COMMANDS & JOBS"
echo "================================================"
echo ""

echo "--- CUSTOM COMMANDS ---"
find app/Console/Commands -type f -name "*.php" 2>/dev/null | sort
echo ""

echo "--- WATCHTOWER COMMANDS ---"
find app/Console/Commands -type f -name "*.php" -exec grep -li "watchtower\|monitor\|metric\|health" {} \; 2>/dev/null | sort
echo ""

echo "--- JOBS ---"
find app/Jobs -type f -name "*.php" 2>/dev/null | sort
echo ""

echo "--- WATCHTOWER JOBS ---"
find app/Jobs -type f -name "*.php" -exec grep -li "watchtower\|monitor\|metric\|health" {} \; 2>/dev/null | sort
echo ""

echo "--- SCHEDULED TASKS ---"
php artisan schedule:list 2>&1
echo ""

echo "--- FAILED JOBS ---"
php artisan queue:failed 2>&1
echo ""

# ============================================
# SECTION 13: EVENTS & LISTENERS
# ============================================
echo "================================================"
echo "SECTION 13: EVENTS & LISTENERS"
echo "================================================"
echo ""

echo "--- EVENTS ---"
find app/Events -type f -name "*.php" 2>/dev/null | sort
echo ""

echo "--- LISTENERS ---"
find app/Listeners -type f -name "*.php" 2>/dev/null | sort
echo ""

echo "--- EVENT SERVICE PROVIDER ---"
if [ -f "app/Providers/EventServiceProvider.php" ]; then
    grep -A 30 "listen" app/Providers/EventServiceProvider.php | head -40
fi
echo ""

# ============================================
# SECTION 14: CACHE AUDIT
# ============================================
echo "================================================"
echo "SECTION 14: CACHE AUDIT"
echo "================================================"
echo ""

echo "--- BOOTSTRAP CACHE ---"
if [ -d "bootstrap/cache" ]; then
    ls -la bootstrap/cache/ 2>/dev/null
    echo ""
    echo "Cached routes:"
    if [ -f "bootstrap/cache/routes-v7.php" ]; then
        echo "  Routes cache EXISTS - routes may be cached from old state!"
        ls -la bootstrap/cache/routes-v7.php
    fi
    echo ""
    echo "Cached config:"
    if [ -f "bootstrap/cache/config.php" ]; then
        echo "  Config cache EXISTS"
        ls -la bootstrap/cache/config.php
    fi
else
    echo "No bootstrap/cache directory"
fi
echo ""

echo "--- WHAT WOULD BE CLEARED ---"
php artisan optimize:clear --dry-run 2>&1 || echo "Command not available, listing cache commands:"
php artisan list 2>&1 | grep cache
echo ""

# ============================================
# SECTION 15: BACKUP ANALYSIS
# ============================================
echo "================================================"
echo "SECTION 15: BACKUP ANALYSIS"
echo "================================================"
echo ""

if [ -d "$BACKUP_DIR" ]; then
    echo "--- BACKUP DIRECTORY STRUCTURE ---"
    find "$BACKUP_DIR" -maxdepth 3 -type f -name "*.php" 2>/dev/null | head -50
    echo ""
    
    echo "--- BACKUP GIT INFO ---"
    if [ -d "$BACKUP_DIR/.git" ]; then
        cd "$BACKUP_DIR"
        echo "Backup last commit:"
        git log --oneline -5 2>&1
        echo ""
        echo "Backup branch:"
        git branch --show-current 2>&1
        cd "$PWD"
    else
        echo "Backup is not a git repository - checking for git bundle/archive"
        find "$BACKUP_DIR" -name "*.git" -o -name "*.bundle" -o -name "git-info.txt" 2>/dev/null
    fi
    echo ""
    
    echo "--- BACKUP vs CURRENT: CONTROLLERS ---"
    echo "Controllers in backup but missing from current:"
    if [ -d "$BACKUP_DIR/app/Http/Controllers" ]; then
        for file in $(find "$BACKUP_DIR/app/Http/Controllers" -type f -name "*.php" 2>/dev/null); do
            rel_path=${file#$BACKUP_DIR/}
            if [ ! -f "$rel_path" ]; then
                echo "  MISSING: $rel_path"
            fi
        done
    fi
    echo ""
    
    echo "Controllers in current but not in backup:"
    for file in $(find app/Http/Controllers -type f -name "*.php" 2>/dev/null); do
        if [ ! -f "$BACKUP_DIR/$file" ]; then
            echo "  NEW: $file"
        fi
    done
    echo ""
    
    echo "--- BACKUP vs CURRENT: ROUTES ---"
    if [ -f "$BACKUP_DIR/routes/api.php" ]; then
        echo "API routes diff summary:"
        diff -u "$BACKUP_DIR/routes/api.php" routes/api.php 2>/dev/null | head -50 || echo "  Routes differ or file missing"
    fi
    echo ""
    
    echo "--- BACKUP vs CURRENT: SERVICES ---"
    echo "Services in backup but missing from current:"
    if [ -d "$BACKUP_DIR/app/Services" ]; then
        for file in $(find "$BACKUP_DIR/app/Services" -type f -name "*.php" 2>/dev/null); do
            rel_path=${file#$BACKUP_DIR/}
            if [ ! -f "$rel_path" ]; then
                echo "  MISSING: $rel_path"
            fi
        done
    fi
    echo ""
else
    echo "WARNING: No backup directory at $BACKUP_DIR"
    echo "Checking for alternative backup locations..."
    find .. -maxdepth 2 -type d -name "backup*" 2>/dev/null
fi
echo ""

# ============================================
# SECTION 16: GIT DIFF WITH BACKUP
# ============================================
echo "================================================"
echo "SECTION 16: GIT DIFF WITH BACKUP"
echo "================================================"
echo ""

if [ -d "$BACKUP_DIR/.git" ]; then
    echo "--- GIT DIFF: CURRENT vs BACKUP (controller changes) ---"
    cd "$BACKUP_DIR"
    BACKUP_HEAD=$(git rev-parse HEAD 2>/dev/null)
    cd "$PWD"
    if [ ! -z "$BACKUP_HEAD" ]; then
        # Compare controllers
        git diff "$BACKUP_HEAD" HEAD -- app/Http/Controllers/ 2>/dev/null | head -100
        echo ""
        # Compare routes
        git diff "$BACKUP_HEAD" HEAD -- routes/ 2>/dev/null | head -100
        echo ""
        # Compare services
        git diff "$BACKUP_HEAD" HEAD -- app/Services/ 2>/dev/null | head -100
    fi
fi
echo ""

# ============================================
# SECTION 17: RUNTIME VALIDATION (Using Artisan Tinker)
# ============================================
echo "================================================"
echo "SECTION 17: RUNTIME CONTROLLER VALIDATION"
echo "================================================"
echo ""

echo "--- INSTANTIATION TEST (Watchtower controllers) ---"
for controller in $WATCHTOWER_CONTROLLERS; do
    if [ -f "$controller" ]; then
        # Extract namespace and class
        namespace=$(grep "^namespace " "$controller" | sed 's/namespace //' | sed 's/;//')
        class=$(grep -E "^class [A-Z]" "$controller" | head -1 | awk '{print $2}')
        fullclass="${namespace}\\${class}"
        
        echo -n "Testing: $fullclass ... "
        
        # Use PHP directly from project root
        result=$(php -r "
            require 'vendor/autoload.php';
            \$app = require_once 'bootstrap/app.php';
            \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
            try {
                new $fullclass();
                echo 'PASS';
            } catch (Throwable \$e) {
                echo 'FAIL: ' . get_class(\$e) . ' - ' . \$e->getMessage();
            }
        " 2>&1)
        echo "$result"
    fi
done
echo ""

# Alternative using Artisan Tinker
echo "--- TINKER VALIDATION (alternative method) ---"
php artisan tinker --execute="
\$controllers = glob('app/Http/Controllers/*Monitor*.php');
\$controllers = array_merge(\$controllers, glob('app/Http/Controllers/*Watchtower*.php'));
foreach (\$controllers as \$file) {
    \$content = file_get_contents(\$file);
    preg_match('/namespace (.*?);/', \$content, \$ns);
    preg_match('/class (\w+)/', \$content, \$cl);
    if (isset(\$ns[1]) && isset(\$cl[1])) {
        \$class = \$ns[1].'\\\\'.\$cl[1];
        echo \"\$class: \";
        try { new \$class(); echo 'PASS'; }
        catch (Throwable \$e) { echo 'FAIL'; }
        echo PHP_EOL;
    }
}
" 2>&1 || echo "Tinker validation failed - will use PHP method above"
echo ""

# ============================================
# SECTION 18: DEPENDENCY CHAIN ANALYSIS
# ============================================
echo "================================================"
echo "SECTION 18: DEPENDENCY CHAIN MATRIX"
echo "================================================"
echo ""

echo "Controller | Methods | Service | Model | Migration | Middleware | Route | Runtime"
echo "-----------|---------|---------|-------|-----------|------------|-------|--------"

for controller in $WATCHTOWER_CONTROLLERS; do
    if [ -f "$controller" ]; then
        basename=$(basename "$controller" .php)
        namespace=$(grep "^namespace " "$controller" | sed 's/namespace //' | sed 's/;//')
        class=$(grep -E "^class [A-Z]" "$controller" | head -1 | awk '{print $2}')
        fullclass="${namespace}\\${class}"
        
        # Count methods
        method_count=$(grep -cE "public function|protected function|private function" "$controller" 2>/dev/null || echo "0")
        
        # Check services
        service_imports=$(grep "use.*Service" "$controller" 2>/dev/null | wc -l || echo "0")
        if [ "$service_imports" -gt "0" ]; then
            service_status="$service_imports imports"
        else
            service_status="none"
        fi
        
        # Check models
        model_imports=$(grep "use App\\\\Models" "$controller" 2>/dev/null | wc -l || echo "0")
        if [ "$model_imports" -gt "0" ]; then
            model_status="$model_imports imports"
        else
            model_status="none"
        fi
        
        # Check migrations (heuristic)
        migration_status="unknown"
        
        # Check middleware references
        middleware_ref=$(grep -c "middleware\|auth\|can\|Gate" "$controller" 2>/dev/null || echo "0")
        if [ "$middleware_ref" -gt "0" ]; then
            middleware_status="referenced"
        else
            middleware_status="none in controller"
        fi
        
        # Check routes
        route_status="not found"
        for route_file in routes/*.php; do
            if grep -q "$class" "$route_file" 2>/dev/null; then
                route_status="FOUND in $(basename $route_file)"
                break
            fi
        done
        
        # Runtime check
        runtime_status="NOT TESTED"
        
        echo "$basename | $method_count methods | $service_status | $model_status | $migration_status | $middleware_status | $route_status | $runtime_status"
    fi
done
echo ""

# ============================================
# SECTION 19: API CONTRACT MAPPING
# ============================================
echo "================================================"
echo "SECTION 19: API CONTRACT & FRONTEND IMPACT"
echo "================================================"
echo ""

echo "--- CURRENT KIN SAFETY API ENDPOINTS ---"
php artisan route:list 2>&1 | grep -E "api/" | awk '{print $3, $4}' | sort -u
echo ""

echo "--- FRONTEND API USAGE ---"
if [ -d "$FRONTEND_DIR" ]; then
    echo "Checking frontend API calls..."
    
    # Find API service files
    api_files=$(find "$FRONTEND_DIR/src" -type f \( -name "*api*" -o -name "*service*" -o -name "*axios*" \) 2>/dev/null)
    
    if [ ! -z "$api_files" ]; then
        echo "API service files found:"
        echo "$api_files"
        echo ""
        
        echo "ENDPOINT REFERENCES IN FRONTEND:"
        for file in $api_files; do
            echo "File: $file"
            grep -E "/(api|watchtower|monitor|health|metric|safety|dashboard|sos|checkin|incident|alert|contact)" "$file" 2>/dev/null | head -20
            echo ""
        done
    else
        echo "No API service files found in frontend"
        echo "Searching all frontend files for API calls..."
        grep -r "/(api|watchtower|monitor|health)" "$FRONTEND_DIR/src" --include="*.js" --include="*.jsx" --include="*.ts" --include="*.tsx" 2>/dev/null | head -50
    fi
    echo ""
    
    echo "--- FRONTEND ROUTES ---"
    if [ -f "$FRONTEND_DIR/src/routes" ] || [ -f "$FRONTEND_DIR/src/App.jsx" ] || [ -f "$FRONTEND_DIR/src/App.tsx" ]; then
        echo "Frontend route definitions:"
        grep -r "path.*:" "$FRONTEND_DIR/src" --include="*.jsx" --include="*.tsx" --include="*.js" --include="*.ts" 2>/dev/null | grep -v node_modules | head -30
    fi
    echo ""
    
    echo "--- FRONTEND SCREENS/COMPONENTS ---"
    find "$FRONTEND_DIR/src" -type f \( -name "*Watchtower*" -o -name "*Monitor*" -o -name "*Health*" -o -name "*Metric*" -o -name "*Dashboard*" \) 2>/dev/null | sort
    echo ""
else
    echo "Frontend directory not found at $FRONTEND_DIR"
fi
echo ""

# ============================================
# SECTION 20: AUTO-CLASSIFICATION
# ============================================
echo "================================================"
echo "SECTION 20: PRELIMINARY RECOVERY CLASSIFICATION"
echo "================================================"
echo ""

echo "CLASSIFICATION CRITERIA:"
echo "  SAFE: Controller + all dependencies exist, route missing but restorable"
echo "  PARTIAL: Controller exists, some dependencies missing"
echo "  BLOCKED: Controller or critical dependency missing"
echo "  ORPHANED: Routes exist but controller missing"
echo "  REPLACED: Newer implementation found"
echo "  LEGACY: Old implementation, no frontend usage"
echo ""
echo "--- CLASSIFICATION ---"

for controller in $WATCHTOWER_CONTROLLERS; do
    if [ -f "$controller" ]; then
        basename=$(basename "$controller" .php)
        namespace=$(grep "^namespace " "$controller" | sed 's/namespace //' | sed 's/;//')
        class=$(grep -E "^class [A-Z]" "$controller" | head -1 | awk '{print $2}')
        fullclass="${namespace}\\${class}"
        
        # Check dependencies
        has_service=false
        has_model=false
        has_route=false
        has_middleware=true  # Assume true unless proven otherwise
        
        # Check service dependencies
        service_deps=$(grep "use.*Service" "$controller" 2>/dev/null)
        if [ ! -z "$service_deps" ]; then
            service_found=true
            for dep in $(echo "$service_deps" | awk '{print $NF}' | sed 's/;//'); do
                dep_file="app/Services/$(basename $dep).php"
                if [ -f "$dep_file" ]; then
                    has_service=true
                fi
            done
        else
            has_service=true  # No service needed
        fi
        
        # Check model dependencies
        model_deps=$(grep "use App\\\\Models" "$controller" 2>/dev/null)
        if [ ! -z "$model_deps" ]; then
            has_model=false
            for dep in $(echo "$model_deps" | awk '{print $NF}' | sed 's/;//'); do
                dep_file="app/Models/$(basename $dep).php"
                if [ -f "$dep_file" ]; then
                    has_model=true
                fi
            done
        else
            has_model=true  # No model needed
        fi
        
        # Check route
        for route_file in routes/*.php; do
            if grep -q "$class\|$basename" "$route_file" 2>/dev/null; then
                has_route=true
                break
            fi
        done
        
        # Classification
        if $has_service && $has_model && $has_middleware; then
            if $has_route; then
                classification="KEEP (already routed)"
            else
                classification="SAFE (dependencies exist, route restorable)"
            fi
        elif ! $has_service || ! $has_model; then
            if $has_route; then
                classification="ORPHANED (routes exist but dependencies missing)"
            else
                classification="PARTIAL (missing some dependencies)"
            fi
        else
            classification="INVESTIGATE"
        fi
        
        # Check frontend usage
        frontend_use="UNKNOWN"
        if [ -d "$FRONTEND_DIR" ]; then
            if grep -rq "$basename\|$class" "$FRONTEND_DIR/src" 2>/dev/null; then
                frontend_use="USED IN FRONTEND"
            fi
        fi
        
        echo "$basename: $classification | Frontend: $frontend_use"
    fi
done
echo ""

# Check for orphaned routes
echo "--- ORPHANED WATCHTOWER ROUTES (routes without controllers) ---"
php artisan route:list 2>&1 | grep -i "watchtower\|monitor\|health\|metric" | while read route_line; do
    # Extract controller from route
    controller_in_route=$(echo "$route_line" | grep -oP '[\w]+@[\w]+' | head -1)
    if [ ! -z "$controller_in_route" ]; then
        controller_class=$(echo "$controller_in_route" | cut -d@ -f1)
        controller_file="app/Http/Controllers/${controller_class}.php"
        if [ ! -f "$controller_file" ]; then
            echo "ORPHANED: $route_line"
            echo "  Controller missing: $controller_file"
        fi
    fi
done
echo ""

# ============================================
# SECTION 21: ARCHITECTURE ANALYSIS
# ============================================
echo "================================================"
echo "SECTION 21: ARCHITECTURE ANALYSIS"
echo "================================================"
echo ""

echo "--- KEY QUESTIONS ---"
echo ""

# Question 1: Was Watchtower replaced?
echo "Q1: Was Watchtower replaced by another monitoring solution?"
echo "Checking for Horizon, Telescope, or custom monitoring..."
grep -r "horizon\|telescope\|laravel-horizon\|laravel-telescope" composer.json 2>/dev/null
echo ""

# Question 2: Is there a newer implementation?
echo "Q2: Are there newer monitoring/nonitoring controllers?"
find app/Http/Controllers -type f -name "*.php" -newer app/Http/Controllers/Controller.php 2>/dev/null | sort
echo ""

# Question 3: Which features supersede Watchtower?
echo "Q3: Current KIN Safety monitoring features:"
php artisan route:list 2>&1 | grep -E "health|status|ping|dashboard|metrics" | head -20
echo ""

# Question 4: Is Watchtower merged into KIN Safety?
echo "Q4: Checking for merged functionality..."
grep -r "watchtower\|queue.monitor\|api.monitor" app/Http/Controllers/ 2>/dev/null | head -20
echo ""

# Question 5: Git history analysis
echo "Q5: Watchtower development timeline:"
git log --all --oneline --graph --grep="watchtower\|monitor\|metric\|health" -i 2>/dev/null | head -20
echo ""
echo "Q5: When was Watchtower last modified?"
git log --all --oneline --name-only -- "*Watchtower*" "*Monitor*" "*Metric*" "*Health*" 2>/dev/null | head -20
echo ""

echo "================================================"
echo "AUDIT COMPLETE"
echo "Report saved to: $PWD/$REPORT"
echo "================================================"

} > "$REPORT" 2>&1

echo ""
echo "========================================="
echo "Audit script completed"
echo "Report: $PWD/$REPORT"
echo "Size: $(ls -lh "$REPORT" | awk '{print $5}')"
echo "Lines: $(wc -l < "$REPORT")"
echo "========================================="
