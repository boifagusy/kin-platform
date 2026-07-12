#!/data/data/com.termux/files/usr/bin/bash

# PROJECT DISCOVERY ENGINE — Read-only. Builds project registry.
# Separated from Analysis and Planning.

if [ -n "$SDK_ROOT" ]; then
    KERNEL_DIR="$SDK_ROOT/kernel"
else
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    KERNEL_DIR="$(dirname "$(dirname "$(dirname "$SCRIPT_DIR")")")/kernel"
fi

source "$KERNEL_DIR/yaml.sh" 2>/dev/null
source "$KERNEL_DIR/filesystem.sh" 2>/dev/null

REGISTRY_DIR=".kin/intelligence/registry"
SNAPSHOT_DIR=".kin/intelligence/snapshots"
mkdir -p "$REGISTRY_DIR" "$SNAPSHOT_DIR"

# ── Build Service Registry ──
discovery_build_registry() {
    local snapshot="$SNAPSHOT_DIR/scan_$(date +%Y%m%d_%H%M%S).yaml"
    local root; root="$(git rev-parse --show-toplevel 2>/dev/null)" || root="."
    
    echo ""
    echo "═══════════════════════════════════════"
    echo "  PROJECT DISCOVERY — Building Registry"
    echo "═══════════════════════════════════════"
    echo ""
    
    # Discover every service with metadata
    echo "services:" > "$REGISTRY_DIR/services.yaml"
    
    if [ -d "$root/backend/app/Services" ]; then
        while IFS= read -r f; do
            local name namespace class methods status
            name="$(basename "$f" .php)"
            namespace="$(grep "^namespace " "$f" 2>/dev/null | head -1 | sed 's/namespace //;s/;//')"
            class="$(grep "^class " "$f" 2>/dev/null | head -1 | awk '{print $2}')"
            methods="$(grep "public function " "$f" 2>/dev/null | wc -l | tr -d ' ')"
            
            # Determine status
            if grep -q "DEPRECATED\|@deprecated" "$f" 2>/dev/null; then status="deprecated"
            elif grep -q "TODO\|FIXME\|@experimental" "$f" 2>/dev/null; then status="experimental"
            elif [ ! -f "$root/backend/tests/Unit/Services/${name}Test.php" ]; then status="partial"
            else status="implemented"; fi
            
            echo "  $name: $status ($methods methods)"
            
            cat >> "$REGISTRY_DIR/services.yaml" << YAML
  - name: $name
    namespace: ${namespace:-unknown}
    class: ${class:-$name}
    file: ${f#$root/}
    methods: $methods
    status: $status
    has_tests: $([ -f "$root/backend/tests/Unit/Services/${name}Test.php" ] && echo "true" || echo "false")
    has_contract: $([ -f ".sdk/contracts/"*"/${name}.yaml" ] && echo "true" || echo "false")
YAML
        done < <(find "$root/backend/app/Services" -name "*.php" -type f 2>/dev/null)
    fi
    
    # Discover features
    echo "features:" > "$REGISTRY_DIR/features.yaml"
    
    if [ -d "$root/frontend/src/screens" ]; then
        for d in "$root/frontend/src/screens"/*/; do
            [ -d "$d" ] || continue
            local feature; feature="$(basename "$d")"
            local has_controller has_route has_view has_test
            has_controller="$(find "$root/backend/app/Http/Controllers" -iname "*${feature}*" 2>/dev/null | wc -l | tr -d ' ')"
            has_view="$(find "$root/frontend/src/screens/$feature" -name "*.jsx" -o -name "*.tsx" 2>/dev/null | wc -l | tr -d ' ')"
            
            local fstatus="unknown"
            [ "$has_controller" -gt 0 ] && [ "$has_view" -gt 0 ] && fstatus="implemented"
            [ "$has_controller" -gt 0 ] && [ "$has_view" -eq 0 ] && fstatus="partial"
            [ "$has_controller" -eq 0 ] && [ "$has_view" -gt 0 ] && fstatus="partial"
            
            cat >> "$REGISTRY_DIR/features.yaml" << YAML
  - name: $feature
    status: $fstatus
    controllers: $has_controller
    views: $has_view
YAML
        done
    fi
    
    # Save snapshot for comparison
    cp "$REGISTRY_DIR/services.yaml" "$snapshot"
    ln -sf "$(basename "$snapshot")" "$SNAPSHOT_DIR/latest.yaml" 2>/dev/null
    
    echo ""
    echo "  Registry: $REGISTRY_DIR/"
    echo "  Snapshot: $snapshot"
}

# ── Compare snapshots ──
discovery_diff() {
    echo ""
    echo "═══════════════════════════════════════"
    echo "  SNAPSHOT COMPARISON"
    echo "═══════════════════════════════════════"
    
    local snapshots=($(ls -1t "$SNAPSHOT_DIR"/scan_*.yaml 2>/dev/null))
    
    if [ ${#snapshots[@]} -lt 2 ]; then
        echo "  Need at least 2 snapshots to compare."
        echo "  Run: ai discovery build"
        return
    fi
    
    echo "  Latest:  $(basename "${snapshots[0]}")"
    echo "  Previous: $(basename "${snapshots[1]}")"
    echo ""
    
    # Show what's new
    local new; new=$(diff "${snapshots[1]}" "${snapshots[0]}" 2>/dev/null | grep "^>")
    if [ -n "$new" ]; then
        echo "  New since last scan:"
        echo "$new" | head -10 | while read line; do echo "    $line"; done
    else
        echo "  No changes detected."
    fi
}

# ── Investigate a feature ──
discovery_investigate() {
    local target="$1"
    
    echo ""
    echo "═══════════════════════════════════════"
    echo "  INVESTIGATION: $target"
    echo "═══════════════════════════════════════"
    echo ""
    
    local root; root="$(git rev-parse --show-toplevel 2>/dev/null)" || root="."
    local total=0
    
    echo "  References found:"
    if [ -d "$root/backend" ]; then
        grep -rl "$target" "$root/backend/app" "$root/backend/routes" "$root/backend/resources" 2>/dev/null | while read f; do
            echo "    • ${f#$root/}"
        done
        total=$(grep -rl "$target" "$root/backend/app" "$root/backend/routes" "$root/backend/resources" 2>/dev/null | wc -l | tr -d ' ')
    fi
    
    echo ""
    echo "  Files affected: ${total:-0}"
    echo "  Risk: $([ "${total:-0}" -gt 10 ] && echo "HIGH" || echo "MEDIUM")"
    echo "  Recommendation: $([ "${total:-0}" -gt 10 ] && echo "Requires approval before modification" || echo "Safe to proceed with investigation")"
}

# ── Confidence scoring ──
discovery_confidence() {
    echo ""
    echo "═══════════════════════════════════════"
    echo "  CONFIDENCE SCORES"
    echo "═══════════════════════════════════════"
    
    local services contracts tests routes
    services=$(find backend/app/Services -name "*.php" -type f 2>/dev/null | wc -l | tr -d ' ')
    contracts=$(find .sdk/contracts -name "*.yaml" -type f 2>/dev/null | wc -l | tr -d ' ')
    tests=$(find backend/tests -name "*Test.php" -type f 2>/dev/null | wc -l | tr -d ' ')
    routes=$(grep -c "Route::" backend/routes/*.php 2>/dev/null)
    
    local svc_conf=$([ "$services" -gt 0 ] && echo 98 || echo 0)
    local ct_conf=$([ "$contracts" -gt 0 ] && echo $(( contracts * 100 / (services + 1) )) || echo 0)
    local test_conf=$([ "$tests" -gt 0 ] && echo $(( tests * 100 / (services + 1) )) || echo 0)
    local overall=$(( (svc_conf + ct_conf + test_conf) / 3 ))
    
    echo "  Architecture: ${svc_conf}%"
    echo "  Contracts:    ${ct_conf}%"
    echo "  Tests:        ${test_conf}%"
    echo "  ─────────────────"
    echo "  Overall:      ${overall}%"
    echo ""
    
    if [ "$overall" -lt 85 ]; then
        echo "  ⚠️  Confidence below 85% — recommend further investigation"
    else
        echo "  ✅ Confidence sufficient for implementation"
    fi
}

# Dispatch
case "${1:-build}" in
    build)     discovery_build_registry ;;
    diff)      discovery_diff ;;
    investigate) discovery_investigate "${2:-}" ;;
    confidence) discovery_confidence ;;
    *)
        echo "Usage: ai discovery [build|diff|investigate|confidence]"
        echo "  ai discovery build        Build project registry"
        echo "  ai discovery diff         Compare snapshots"
        echo "  ai discovery investigate  Deep-dive a feature"
        echo "  ai discovery confidence   Confidence scores"
        ;;
esac
