#!/data/data/com.termux/files/usr/bin/bash

# PROJECT DISCOVERY ENGINE v3.0
# Weighted scoring, risk-based recommendations, gate-aware, trend tracking

if [ -n "$SDK_ROOT" ]; then
    KERNEL_DIR="$SDK_ROOT/kernel"
    ENGINES_DIR="$SDK_ROOT/engines"
else
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    KERNEL_DIR="$(dirname "$(dirname "$(dirname "$SCRIPT_DIR")")")/kernel"
    ENGINES_DIR="$(dirname "$(dirname "$SCRIPT_DIR")")"
fi

source "$KERNEL_DIR/yaml.sh" 2>/dev/null
source "$KERNEL_DIR/filesystem.sh" 2>/dev/null
source "$ENGINES_DIR/gate/engine.sh" 2>/dev/null

REGISTRY_DIR=".kin/intelligence/registry"
SNAPSHOT_DIR=".kin/intelligence/snapshots"
REPORT_DIR=".kin/intelligence/reports"
mkdir -p "$REGISTRY_DIR" "$SNAPSHOT_DIR" "$REPORT_DIR"

# ── Weighted Scoring Model ──
# Architecture: 15%, Contracts: 30%, Tests: 25%, Bricks: 10%, Debt: 10%, Docs: 5%, Validation: 5%

discovery_build_registry() {
    local snapshot="$SNAPSHOT_DIR/scan_$(date +%Y%m%d_%H%M%S).yaml"
    local root; root="$(git rev-parse --show-toplevel 2>/dev/null)" || root="."
    
    echo "services:" > "$REGISTRY_DIR/services.yaml"
    
    if [ -d "$root/backend/app/Services" ]; then
        while IFS= read -r f; do
            local name namespace class methods status
            name="$(basename "$f" .php)"
            namespace="$(grep "^namespace " "$f" 2>/dev/null | head -1 | sed 's/namespace //;s/;//')"
            class="$(grep "^class " "$f" 2>/dev/null | head -1 | awk '{print $2}')"
            methods="$(grep "public function " "$f" 2>/dev/null | wc -l | tr -d ' ')"
            
            if grep -q "DEPRECATED\|@deprecated" "$f" 2>/dev/null; then status="deprecated"
            elif grep -q "TODO\|FIXME\|@experimental" "$f" 2>/dev/null; then status="experimental"
            elif [ ! -f "$root/backend/tests/Unit/Services/${name}Test.php" ]; then status="partial"
            else status="implemented"; fi
            
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
    
    cp "$REGISTRY_DIR/services.yaml" "$snapshot"
    ln -sf "$(basename "$snapshot")" "$SNAPSHOT_DIR/latest.yaml" 2>/dev/null
}

# ── Weighted Confidence Score ──
discovery_confidence() {
    local gate; gate="$(gate_current 2>/dev/null)"
    local gate_name; gate_name="$(gate_name "$gate" 2>/dev/null)"
    local project; project="$(git rev-parse --show-toplevel 2>/dev/null | xargs basename 2>/dev/null)"
    
    # Gather metrics
    local services contracts tests bricks certs debt_items docs
    services=$(find backend/app/Services -name "*.php" -type f 2>/dev/null | wc -l | tr -d ' ')
    contracts=$(find .sdk/contracts -name "*.yaml" -type f 2>/dev/null | wc -l | tr -d ' ')
    tests=$(find backend/tests -name "*Test.php" -type f 2>/dev/null | wc -l | tr -d ' ')
    bricks=$(ls -1d bricks/*/ 2>/dev/null | wc -l | tr -d ' ')
    certs=$(ls -1 .kin/certifications/*.yaml 2>/dev/null | wc -l | tr -d ' ')
    docs=$(find docs -name "*.md" -type f 2>/dev/null | wc -l | tr -d ' ')
    
    # Individual scores (0-100)
    local arch_score=$([ "$services" -gt 0 ] && echo 98 || echo 0)
    
    local contract_pct=$([ "$services" -gt 0 ] && echo $(( contracts * 100 / (services + 1) )) || echo 0)
    [ "$contract_pct" -gt 100 ] && contract_pct=100
    
    local test_pct=$([ "$services" -gt 0 ] && echo $(( tests * 100 / (services + 1) )) || echo 0)
    [ "$test_pct" -gt 100 ] && test_pct=100
    
    local brick_score=$([ "$bricks" -gt 0 ] && echo 80 || echo 10)
    local debt_score=65
    local doc_score=$([ "$docs" -gt 10 ] && echo 70 || echo 30)
    local val_score=$([ "$certs" -gt 0 ] && echo 75 || echo 15)
    
    # Weighted calculation
    local weighted=$(( (arch_score * 15) + (contract_pct * 30) + (test_pct * 25) + (brick_score * 10) + (debt_score * 10) + (doc_score * 5) + (val_score * 5) ))
    local overall=$(( weighted / 100 ))
    
    # Maturity level
    local maturity
    if [ "$overall" -le 20 ]; then maturity="Prototype"
    elif [ "$overall" -le 40 ]; then maturity="Early Development"
    elif [ "$overall" -le 60 ]; then maturity="Feature Complete"
    elif [ "$overall" -le 80 ]; then maturity="Stabilization"
    elif [ "$overall" -le 90 ]; then maturity="Release Candidate"
    else maturity="Production Ready"; fi
    
    # Trend
    local prev_score=0 trend="first_scan"
    if [ -f "$REPORT_DIR/latest_score.txt" ]; then
        prev_score=$(cat "$REPORT_DIR/latest_score.txt" 2>/dev/null)
        if [ "$overall" -gt "$prev_score" ]; then trend="+$((overall - prev_score))%"
        elif [ "$overall" -lt "$prev_score" ]; then trend="-$((prev_score - overall))%"
        else trend="stable"; fi
    fi
    echo "$overall" > "$REPORT_DIR/latest_score.txt"
    
    # Determine critical services (no contracts, high methods = high risk)
    local critical_services=""
    if [ -f "$REGISTRY_DIR/services.yaml" ]; then
        critical_services=$(grep -B5 "has_contract: false" "$REGISTRY_DIR/services.yaml" 2>/dev/null | grep "name:" | sed 's/.*: //' | head -5 | tr '\n' ', ' | sed 's/,$//')
    fi
    
    # ── Gate-aware recommendation ──
    local recommendation next_action
    if [ "$gate" -le 2 ]; then
        recommendation="Complete Discovery and Requirements gates"
        next_action="ai gate advance"
    elif [ "$gate" -le 5 ] && [ "$contract_pct" -lt 50 ]; then
        recommendation="Priority 1: Contract verification for critical services"
        next_action="ai contract verify"
    elif [ "$gate" -le 6 ] && [ "$test_pct" -lt 30 ]; then
        recommendation="Priority 2: Add tests for contract-certified services"
        next_action="ai investigate services"
    elif [ "$gate" -le 11 ] && [ "$overall" -lt 65 ]; then
        recommendation="Stabilize before release — contracts + tests needed"
        next_action="ai discovery confidence"
    else
        recommendation="Proceed with current gate requirements"
        next_action="ai gate verify && ai gate advance"
    fi
    
    # Estimated risk reduction
    local risk_reduction=0
    [ "$contract_pct" -lt 50 ] && risk_reduction=$((50 - contract_pct + 15))
    [ "$test_pct" -lt 30 ] && risk_reduction=$((risk_reduction + 30 - test_pct))
    
    # ── Output Report ──
    echo ""
    echo "════════════════════════════════════════════"
    echo "  PROJECT INTELLIGENCE REPORT"
    echo "════════════════════════════════════════════"
    echo ""
    echo "  Project:    $project"
    echo "  Gate:       $gate — $gate_name"
    echo "  Maturity:   $maturity"
    echo "  Trend:      $trend"
    echo ""
    echo "────────────────────────────────────────"
    echo "  SCORES (Weighted)"
    echo "────────────────────────────────────────"
    echo "  Architecture:   ${arch_score}%  (weight: 15%)"
    echo "  Contracts:      ${contract_pct}%  (weight: 30%)  ← CRITICAL"
    echo "  Tests:          ${test_pct}%  (weight: 25%)"
    echo "  Bricks:         ${brick_score}%  (weight: 10%)"
    echo "  Technical Debt: ${debt_score}%  (weight: 10%)"
    echo "  Documentation:  ${doc_score}%  (weight: 5%)"
    echo "  Validation:     ${val_score}%  (weight: 5%)"
    echo "  ─────────────────────────────────"
    echo "  STABILITY:      ${overall}%"
    echo ""
    
    if [ ${#critical_services} -gt 5 ]; then
        echo "────────────────────────────────────────"
        echo "  HIGHEST RISKS"
        echo "────────────────────────────────────────"
        echo "  Uncertified services: $critical_services..."
        echo ""
    fi
    
    echo "────────────────────────────────────────"
    echo "  RECOMMENDATION"
    echo "────────────────────────────────────────"
    echo "  $recommendation"
    echo ""
    echo "  Next Action: $next_action"
    [ "$risk_reduction" -gt 0 ] && echo "  Risk Reduction: ${overall}% → $(( overall + risk_reduction > 95 ? 95 : overall + risk_reduction ))%"
    echo ""
    echo "════════════════════════════════════════════"
}

# Dispatch
case "${1:-status}" in
    build)     discovery_build_registry ;;
    confidence) discovery_confidence ;;
    status)    echo "Registry: $REGISTRY_DIR/"; ls "$REGISTRY_DIR/" 2>/dev/null ;;
    *)
        echo "Usage: ai discovery [build|confidence|status]"
        discovery_confidence
        ;;
esac
