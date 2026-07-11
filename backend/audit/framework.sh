#!/data/data/com.termux/files/usr/bin/bash
# KIN Recovery Framework - Read-Only Audit Engine
# All functions guaranteed to never modify project files

set -euo pipefail  # Exit on error, undefined vars, pipe failures

# ============================================
# SAFETY LOCKS
# ============================================
READONLY_CHECK() {
    echo "=== READ-ONLY AUDIT MODE ==="
    echo "This framework will NOT modify any project files."
    echo ""
    
    # Verify we're in the right place
    if [ ! -f "artisan" ]; then
        echo "ERROR: Must run from Laravel project root"
        exit 1
    fi
    
    # Check for uncommitted changes (warning only)
    if ! git diff --quiet 2>/dev/null; then
        echo "WARNING: Working tree has uncommitted changes"
        echo "Audit may reflect work-in-progress state"
        echo ""
    fi
    
    # Prevent accidental modification commands
    alias sed='echo "BLOCKED: sed -i disabled in audit mode"'
    alias mv='echo "BLOCKED: mv disabled in audit mode"'
    alias cp='echo "BLOCKED: cp disabled in audit mode"'
    alias rm='echo "BLOCKED: rm disabled in audit mode"'
    alias git-checkout='echo "BLOCKED: git checkout disabled in audit mode"'
    alias git-restore='echo "BLOCKED: git restore disabled in audit mode"'
}

# ============================================
# PROJECT FINGERPRINT
# ============================================
FINGERPRINT() {
    local report_dir="$1"
    local fp_file="$report_dir/fingerprint.txt"
    
    echo "Generating project fingerprint..."
    
    {
        echo "=== KIN PROJECT FINGERPRINT ==="
        echo "Timestamp: $(date -Iseconds)"
        echo ""
        echo "--- Laravel ---"
        php artisan --version 2>/dev/null || echo "Unknown"
        echo ""
        echo "--- PHP ---"
        php -v 2>/dev/null | head -1
        echo ""
        echo "--- Git ---"
        echo "Branch: $(git branch --show-current 2>/dev/null || echo 'N/A')"
        echo "Commit: $(git rev-parse HEAD 2>/dev/null || echo 'N/A')"
        echo "Last commit date: $(git log -1 --format=%ci 2>/dev/null || echo 'N/A')"
        echo ""
        echo "--- Dependencies ---"
        if [ -f "composer.lock" ]; then
            echo "Composer hash: $(md5sum composer.lock 2>/dev/null | cut -d' ' -f1 || echo 'N/A')"
        fi
        if [ -f "../frontend/package-lock.json" ]; then
            echo "NPM hash: $(md5sum ../frontend/package-lock.json 2>/dev/null | cut -d' ' -f1 || echo 'N/A')"
        fi
        echo ""
        echo "--- Environment ---"
        echo "APP_ENV: $(grep '^APP_ENV=' .env 2>/dev/null | cut -d= -f2 || echo 'N/A')"
        echo "APP_KEY exists: $([ -z "$(grep '^APP_KEY=' .env 2>/dev/null | cut -d= -f2)" ] && echo 'NO' || echo 'YES')"
    } > "$fp_file"
    
    echo "Fingerprint: $fp_file"
}

# ============================================
# PORTABLE FILE INFO (No stat -c)
# ============================================
FILE_INFO() {
    local file="$1"
    if [ -f "$file" ]; then
        echo "  Size: $(ls -lh "$file" | awk '{print $5}')"
        echo "  Modified: $(ls -l "$file" | awk '{print $6, $7, $8}')"
    else
        echo "  FILE NOT FOUND"
    fi
}

# ============================================
# PHP-BASED JSON PARSER (No Python dependency)
# ============================================
PHP_JSON() {
    local json_file="$1"
    local expression="$2"
    
    php -r "
    \$data = json_decode(file_get_contents('$json_file'), true);
    if (\$data === null) {
        echo 'JSON_PARSE_ERROR';
        exit(1);
    }
    $expression
    " 2>/dev/null || echo "PHP_JSON_ERROR"
}

# ============================================
# NAMESPACE EXTRACTOR
# ============================================
GET_NAMESPACE() {
    local file="$1"
    grep "^namespace " "$file" 2>/dev/null | sed 's/namespace //' | sed 's/;//' | head -1
}

# ============================================
# CLASS EXTRACTOR
# ============================================
GET_CLASS() {
    local file="$1"
    grep -E "^(abstract |final )?class [A-Z]" "$file" 2>/dev/null | head -1 | awk '{for(i=1;i<=NF;i++) if($i=="class") print $(i+1)}'
}

# ============================================
# METHOD EXTRACTOR
# ============================================
GET_METHODS() {
    local file="$1"
    grep -E "^\s*(public|protected|private) function" "$file" 2>/dev/null | sed 's/.*function //' | sed 's/(.*//' | sort -u
}

# ============================================
# ROUTE CONFLICT DETECTOR
# ============================================
DETECT_ROUTE_CONFLICTS() {
    local temp_dir="$1"
    
    echo "Scanning for route conflicts..."
    
    php artisan route:list --json 2>/dev/null > "$temp_dir/routes.json"
    
    if [ -f "$temp_dir/routes.json" ] && [ -s "$temp_dir/routes.json" ]; then
        php -r '
        $routes = json_decode(file_get_contents("'$temp_dir/routes.json'"), true);
        $uris = [];
        $conflicts = [];
        
        foreach ($routes as $route) {
            $uri = $route["uri"];
            $methods = implode(",", $route["methods"]);
            $key = "$methods $uri";
            
            if (isset($uris[$key])) {
                $conflicts[] = [
                    "uri" => $uri,
                    "methods" => $methods,
                    "action1" => $uris[$key],
                    "action2" => $route["action"]
                ];
            } else {
                $uris[$key] = $route["action"];
            }
        }
        
        if (empty($conflicts)) {
            echo "No route conflicts detected.\n";
        } else {
            echo "ROUTE CONFLICTS FOUND:\n";
            foreach ($conflicts as $c) {
                echo "  [{$c["methods"]}] {$c["uri"]}\n";
                echo "    Action 1: {$c["action1"]}\n";
                echo "    Action 2: {$c["action2"]}\n\n";
            }
        }
        '
    fi
}

# ============================================
# CONTROLLER METHOD VALIDATOR
# ============================================
VALIDATE_CONTROLLER_METHODS() {
    local temp_dir="$1"
    
    echo "Validating controller methods..."
    
    php artisan route:list --json 2>/dev/null > "$temp_dir/routes.json"
    
    if [ -f "$temp_dir/routes.json" ] && [ -s "$temp_dir/routes.json" ]; then
        php -r '
        $routes = json_decode(file_get_contents("'$temp_dir/routes.json'"), true);
        $errors = [];
        
        foreach ($routes as $route) {
            $action = $route["action"];
            if (strpos($action, "@") !== false) {
                list($controller, $method) = explode("@", $action);
                
                // Convert namespace to file path
                $file = str_replace("\\", "/", $controller);
                $file = str_replace("App/", "app/", $file) . ".php";
                
                if (!file_exists($file)) {
                    $errors[] = [
                        "type" => "controller_missing",
                        "controller" => $controller,
                        "method" => $method,
                        "uri" => $route["uri"]
                    ];
                    continue;
                }
                
                // Check if method exists in controller
                $content = file_get_contents($file);
                $pattern = "/function\s+$method\s*\(/";
                if (!preg_match($pattern, $content)) {
                    $errors[] = [
                        "type" => "method_missing",
                        "controller" => $controller,
                        "method" => $method,
                        "uri" => $route["uri"],
                        "file" => $file
                    ];
                }
            }
        }
        
        if (empty($errors)) {
            echo "All controller methods validated successfully.\n";
        } else {
            echo "VALIDATION ERRORS:\n";
            foreach ($errors as $e) {
                echo "  {$e["type"]}: {$e["uri"]}\n";
                echo "    Controller: {$e["controller"]}\n";
                echo "    Method: {$e["method"]}\n";
                if (isset($e["file"])) {
                    echo "    File exists but method missing: {$e["file"]}\n";
                }
                echo "\n";
            }
        }
        '
    fi
}

# ============================================
# FEATURE HEALTH SCORER
# ============================================
SCORE_FEATURE() {
    local feature_name="$1"
    local controller_pattern="$2"
    local route_pattern="$3"
    local frontend_pattern="$4"
    
    local score=0
    local max_score=0
    
    # Check controllers
    max_score=$((max_score + 25))
    if find app/Http/Controllers -type f -name "$controller_pattern" 2>/dev/null | grep -q .; then
        score=$((score + 25))
        controller_status="✓"
    else
        controller_status="✗"
    fi
    
    # Check routes
    max_score=$((max_score + 25))
    if php artisan route:list 2>/dev/null | grep -qi "$route_pattern"; then
        score=$((score + 25))
        route_status="✓"
    else
        route_status="✗"
    fi
    
    # Check services
    max_score=$((max_score + 25))
    if find app/Services -type f -name "*.php" -exec grep -li "$route_pattern" {} \; 2>/dev/null | grep -q .; then
        score=$((score + 25))
        service_status="✓"
    else
        service_status="✗"
    fi
    
    # Check frontend
    max_score=$((max_score + 25))
    if [ -d "../frontend/src" ]; then
        if grep -rq "$frontend_pattern" "../frontend/src" --include="*.js" --include="*.jsx" --include="*.ts" --include="*.tsx" 2>/dev/null; then
            score=$((score + 25))
            frontend_status="✓"
        else
            frontend_status="✗"
        fi
    else
        frontend_status="N/A"
    fi
    
    local percentage=$((score * 100 / max_score))
    
    # Determine status
    if [ $percentage -ge 90 ]; then
        status="PRODUCTION"
    elif [ $percentage -ge 60 ]; then
        status="RECOVERABLE"
    elif [ $percentage -ge 30 ]; then
        status="PARTIAL"
    else
        status="MISSING"
    fi
    
    echo "$feature_name | $percentage% | $controller_status | $route_status | $service_status | $frontend_status | $status"
}

echo "Framework loaded successfully"
