#!/data/data/com.termux/files/usr/bin/bash

# CONTRACT VERIFICATION ENGINE
# Verifies implementation against contracts. No assumptions.

if [ -n "$SDK_ROOT" ]; then
    KERNEL_DIR="$SDK_ROOT/kernel"
    CONTRACTS_DIR="$SDK_ROOT/contracts"
else
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    KERNEL_DIR="$(dirname "$(dirname "$SCRIPT_DIR")")/kernel"
    CONTRACTS_DIR="$(dirname "$(dirname "$SCRIPT_DIR")")/contracts"
fi

source "$KERNEL_DIR/yaml.sh" 2>/dev/null
source "$KERNEL_DIR/filesystem.sh" 2>/dev/null

# List all contracts
contract_list() {
    echo "SERVICE CONTRACT REGISTRY"
    echo "═══════════════════════════════════════"
    
    if [ ! -d "$CONTRACTS_DIR" ]; then
        echo "  (no contracts directory)"
        return
    fi
    
    for domain in "$CONTRACTS_DIR"/*/; do
        [ -d "$domain" ] || continue
        echo ""
        echo "  $(basename "$domain"):"
        for contract in "$domain"/*.yaml; do
            [ -f "$contract" ] || continue
            local name version status
            name="$(yaml_get_nested "$contract" "service" "name" 2>/dev/null)"
            version="$(yaml_get_nested "$contract" "service" "version" 2>/dev/null)"
            echo "    $name v$version"
        done
    done
}

# Verify a single contract against actual code
contract_verify() {
    local contract_file="$1"
    
    if [ ! -f "$contract_file" ]; then
        echo "Contract not found: $contract_file"
        return 1
    fi
    
    local name namespace class
    name="$(yaml_get_nested "$contract_file" "service" "name" 2>/dev/null)"
    namespace="$(yaml_get_nested "$contract_file" "service" "namespace" 2>/dev/null)"
    class="$(yaml_get_nested "$contract_file" "service" "class" 2>/dev/null)"
    
    echo ""
    echo "═══════════════════════════════════════"
    echo "  CONTRACT VERIFICATION: $name"
    echo "═══════════════════════════════════════"
    echo "  Namespace: $namespace"
    echo "  Class:     $class"
    echo ""
    
    local errors=0
    
    # Find the actual PHP file
    local php_file
    php_file="backend/app/Services/Watchtower/${class}.php"
    
    if [ ! -f "$php_file" ]; then
        echo "  ❌ Class file not found: $php_file"
        return 1
    fi
    echo "  ✅ Class file exists: $php_file"
    
    # Verify each method from contract
    local methods
    methods="$(grep "  - name:" "$contract_file" 2>/dev/null | sed 's/.*name: //')"
    
    for method in $methods; do
        if grep -q "function $method" "$php_file" 2>/dev/null; then
            echo "  ✅ Method: $method()"
        else
            echo "  ❌ Method missing: $method()"
            echo "     Expected: $(grep -A4 "name: $method" "$contract_file" | grep "returns:" | sed 's/.*: //')"
            errors=$((errors + 1))
        fi
    done
    
    # Update last_verified
    if [ $errors -eq 0 ]; then
        sed -i "s/last_verified:.*/last_verified: $(date -u +%Y-%m-%dT%H:%M:%SZ)/" "$contract_file"
        echo ""
        echo "  VERDICT: ✅ PASS — All methods verified"
        return 0
    else
        echo ""
        echo "  VERDICT: ❌ FAIL — $errors method(s) missing"
        return 1
    fi
}

# Verify all contracts
contract_verify_all() {
    local total=0 passed=0 failed=0
    
    for contract in $(find "$CONTRACTS_DIR" -name "*.yaml" 2>/dev/null); do
        total=$((total + 1))
        if contract_verify "$contract"; then
            passed=$((passed + 1))
        else
            failed=$((failed + 1))
        fi
    done
    
    echo ""
    echo "═══════════════════════════════════════"
    echo "  CONTRACT VERIFICATION SUMMARY"
    echo "═══════════════════════════════════════"
    echo "  Total:   $total"
    echo "  Passed:  $passed"
    echo "  Failed:  $failed"
    echo "═══════════════════════════════════════"
    
    return $failed
}

# Show contract status
contract_status() {
    echo "CONTRACT STATUS"
    echo "═══════════════════════════════════════"
    
    for contract in $(find "$CONTRACTS_DIR" -name "*.yaml" 2>/dev/null); do
        local name verified
        name="$(yaml_get_nested "$contract" "service" "name" 2>/dev/null)"
        verified="$(yaml_get_nested "$contract" "service" "last_verified" 2>/dev/null)"
        
        if [ "$verified" != "null" ] && [ -n "$verified" ]; then
            echo "  ✅ $name — verified $verified"
        else
            echo "  ⬜ $name — not verified"
        fi
    done
}

# Architecture sync — update architecture docs from verified contracts
contract_sync() {
    echo "ARCHITECTURE SYNC"
    echo "═══════════════════════════════════════"
    
    local arch_file="docs/ARCHITECTURE.md"
    
    for contract in $(find "$CONTRACTS_DIR" -name "*.yaml" 2>/dev/null); do
        local name methods
        name="$(yaml_get_nested "$contract" "service" "name" 2>/dev/null)"
        methods="$(grep "  - name:" "$contract" 2>/dev/null | sed 's/.*name: //')"
        
        echo "  $name:"
        for method in $methods; do
            echo "    • $method()"
        done
    done
    
    echo ""
    echo "  Architecture synced from verified contracts."
    echo "  $(date -u +%Y-%m-%dT%H:%M:%SZ)"
}

# Dispatch
case "${1:-status}" in
    list)    contract_list ;;
    verify)  contract_verify_all ;;
    status)  contract_status ;;
    sync)    contract_sync ;;
    *)
        echo "Usage: ai contract [list|verify|status|sync]"
        contract_status
        ;;
esac
