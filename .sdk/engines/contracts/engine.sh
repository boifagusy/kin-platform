#!/data/data/com.termux/files/usr/bin/bash

# CONTRACT ENGINE v2.0
# Verified Contracts Before Implementation
# No assumptions. Machine-readable artifacts.

if [ -n "$SDK_ROOT" ]; then
    KERNEL_DIR="$SDK_ROOT/kernel"
    ENGINES_DIR="$SDK_ROOT/engines"
else
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    ENGINES_DIR="$(dirname "$SCRIPT_DIR")"
    KERNEL_DIR="$(dirname "$ENGINES_DIR")/kernel"
fi

source "$KERNEL_DIR/yaml.sh" 2>/dev/null
source "$KERNEL_DIR/filesystem.sh" 2>/dev/null

CONTRACTS_DIR=".kin/contracts"
mkdir -p "$CONTRACTS_DIR"

# ── Verify a PHP class exists and has expected methods ──
contract_verify_class() {
    local class_path="$1"
    local expected_methods="${2:-}"
    local now; now="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
    
    local class_name
    class_name="$(basename "$class_path" .php)"
    
    echo ""
    echo "═══════════════════════════════════════"
    echo "  CONTRACT VERIFY: $class_name"
    echo "═══════════════════════════════════════"
    
    # Check file exists
    if [ ! -f "$class_path" ]; then
        echo "  ❌ File not found: $class_path"
        return 1
    fi
    echo "  ✅ File: $class_path"
    
    # Extract namespace
    local namespace
    namespace="$(grep "^namespace " "$class_path" 2>/dev/null | head -1 | sed 's/namespace //;s/;//')"
    echo "  ✅ Namespace: ${namespace:-none}"
    
    # Extract class name
    local actual_class
    actual_class="$(grep "^class " "$class_path" 2>/dev/null | head -1 | awk '{print $2}')"
    echo "  ✅ Class: ${actual_class:-unknown}"
    
    # Verify methods
    local errors=0
    local verified_methods=""
    
    if [ -n "$expected_methods" ]; then
        for method in $expected_methods; do
            if grep -q "function $method" "$class_path" 2>/dev/null; then
                # Extract return type
                local return_type
                return_type="$(grep "function $method" "$class_path" 2>/dev/null | grep -oP ':\s*\K\S+' | head -1)"
                echo "  ✅ Method: $method() → ${return_type:-mixed}"
                verified_methods="$verified_methods $method"
            else
                echo "  ❌ Method missing: $method()"
                errors=$((errors + 1))
            fi
        done
    else
        # Auto-discover all public methods
        echo "  Methods discovered:"
        grep "public function " "$class_path" 2>/dev/null | while read line; do
            local method
            method="$(echo "$line" | sed 's/.*function //;s/(.*//')"
            echo "    • $method()"
            verified_methods="$verified_methods $method"
        done
    fi
    
    # Generate machine-readable artifact
    local artifact="$CONTRACTS_DIR/${class_name}.json"
    cat > "$artifact" << JSON
{
  "class": "$actual_class",
  "namespace": "$namespace",
  "file": "$class_path",
  "verified_at": "$now",
  "methods": "$(echo $verified_methods | tr ' ' ',')",
  "status": "$([ $errors -eq 0 ] && echo "certified" || echo "failed")"
}
JSON
    
    echo ""
    echo "  Artifact: $artifact"
    
    if [ $errors -eq 0 ]; then
        echo "  STATUS: ✅ CERTIFIED"
        return 0
    else
        echo "  STATUS: ❌ $errors method(s) missing"
        return 1
    fi
}

# ── Discover and verify all service classes ──
contract_verify_all() {
    local service_dir="${1:-backend/app/Services}"
    local total=0 passed=0 failed=0
    
    echo "CONTRACT VERIFICATION — $service_dir"
    echo "═══════════════════════════════════════"
    echo ""
    
    if [ ! -d "$service_dir" ]; then
        echo "  Directory not found: $service_dir"
        return 1
    fi
    
    for php_file in $(find "$service_dir" -name "*.php" -type f 2>/dev/null); do
        total=$((total + 1))
        local class_name
        class_name="$(basename "$php_file" .php)"
        
        if contract_verify_class "$php_file" ""; then
            passed=$((passed + 1))
        else
            failed=$((failed + 1))
        fi
    done
    
    # Summary artifact
    local summary="$CONTRACTS_DIR/verification_summary.json"
    cat > "$summary" << JSON
{
  "directory": "$service_dir",
  "verified_at": "$(date -u +%Y-%m-%dT%H:%M:%SZ)",
  "total": $total,
  "passed": $passed,
  "failed": $failed,
  "status": "$([ $failed -eq 0 ] && echo "certified" || echo "incomplete")"
}
JSON
    
    echo ""
    echo "═══════════════════════════════════════"
    echo "  VERIFICATION SUMMARY"
    echo "═══════════════════════════════════════"
    echo "  Total:   $total"
    echo "  Passed:  $passed"
    echo "  Failed:  $failed"
    echo "  Status:  $([ $failed -eq 0 ] && echo "✅ CERTIFIED" || echo "❌ INCOMPLETE")"
    echo "  Summary: $summary"
    echo "═══════════════════════════════════════"
    
    return $failed
}

# ── Show contract artifacts ──
contract_show() {
    local class="$1"
    local artifact="$CONTRACTS_DIR/${class}.json"
    
    if [ -f "$artifact" ]; then
        echo "CONTRACT: $class"
        echo "═══════════════════════════════════════"
        cat "$artifact"
    else
        echo "No contract found for: $class"
        echo "Run: ai contract verify"
    fi
}

# ── List all contracts ──
contract_list() {
    echo "CONTRACT REGISTRY"
    echo "═══════════════════════════════════════"
    
    if [ -d "$CONTRACTS_DIR" ] && [ -n "$(ls -A "$CONTRACTS_DIR" 2>/dev/null)" ]; then
        for artifact in "$CONTRACTS_DIR"/*.json; do
            [ -f "$artifact" ] || continue
            local name status
            name="$(basename "$artifact" .json)"
            status="$(grep -o '"status": "[^"]*"' "$artifact" 2>/dev/null | head -1 | cut -d'"' -f4)"
            echo "  $name — ${status:-unknown}"
        done
    else
        echo "  (no contracts verified)"
    fi
}

# ── Certify a task ──
contract_certify() {
    local task="${1:-task_0}"
    local summary="$CONTRACTS_DIR/verification_summary.json"
    
    if [ -f "$summary" ]; then
        local status
        status="$(grep -o '"status": "[^"]*"' "$summary" 2>/dev/null | cut -d'"' -f4)"
        
        if [ "$status" = "certified" ]; then
            echo "✅ Task '$task' CERTIFIED"
            echo "   Artifact: $summary"
            
            # Update task state
            mkdir -p .kin/state
            cat > .kin/state/task.yaml << YAML
task:
  name: $task
  status: certified
  certified_at: $(date -u +%Y-%m-%dT%H:%M:%SZ)
  artifact: $summary
YAML
            return 0
        else
            echo "❌ Cannot certify — verification incomplete"
            return 1
        fi
    else
        echo "❌ No verification summary found"
        echo "   Run: ai contract verify"
        return 1
    fi
}

# Dispatch
case "${1:-list}" in
    verify)  contract_verify_all "${2:-backend/app/Services}" ;;
    show)    contract_show "${2:-}" ;;
    list)    contract_list ;;
    certify) contract_certify "${2:-task_0}" ;;
    *)
        echo "Usage: ai contract [verify|show|list|certify]"
        contract_list
        ;;
esac
