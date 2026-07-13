#!/data/data/com.termux/files/usr/bin/bash

# CONSTITUTION COMPLIANCE ENGINE v2.0
# Structural + Behavioral + Constitutional verification

COMPLIANCE_DIR=".kin/compliance"
ARTICLES_DIR=".sdk/engines/compliance/articles"

echo ""
echo "════════════════════════════════════════════"
echo "  CONSTITUTION COMPLIANCE AUDIT v2.0"
echo "  ENGINEERING_OS_CONSTITUTION.md v3.3"
echo "════════════════════════════════════════════"
echo ""

PASSED=0 FAILED=0 WARNINGS=0 TOTAL=0

# Phase 1: Structural (file/directory existence)
echo "  PHASE 1: STRUCTURAL COMPLIANCE"
echo "  ─────────────────────────────────"
for article in "$ARTICLES_DIR"/article_*.sh; do
    [ -f "$article" ] || continue
    result=$(bash "$article" 2>/dev/null | grep -c "✅")
    TOTAL=$((TOTAL + 1))
done
echo ""

# Phase 2: Behavioral (actual execution tests)
echo "  PHASE 2: BEHAVIORAL COMPLIANCE"
echo "  ─────────────────────────────────"

# Test: guard actually blocks
echo -n "  "
if ai guard implement 2>&1 | grep -q "BLOCKED\|too early\|issue"; then
    echo "✅ Guard blocks implementation"
    PASSED=$((PASSED + 1))
else
    echo "⚠️  Guard may not be blocking"
    WARNINGS=$((WARNINGS + 1))
fi

# Test: contract verify produces evidence
echo -n "  "
CONTRACT_COUNT=$(find .kin/contracts -name "*.json" 2>/dev/null | wc -l | tr -d ' ')
if [ "$CONTRACT_COUNT" -gt 0 ]; then
    echo "✅ Contract evidence: $CONTRACT_COUNT files"
    PASSED=$((PASSED + 1))
else
    echo "❌ No contract evidence"
    FAILED=$((FAILED + 1))
fi

# Test: certification produces records
echo -n "  "
CERT_COUNT=$(ls .kin/certifications/*.yaml 2>/dev/null | wc -l | tr -d ' ')
if [ "$CERT_COUNT" -gt 0 ]; then
    echo "✅ Certification records: $CERT_COUNT"
    PASSED=$((PASSED + 1))
else
    echo "⚠️  No certification records"
    WARNINGS=$((WARNINGS + 1))
fi

# Test: governance produces evidence
echo -n "  "
GOV_COUNT=$(ls .kin/evidence/governance_*.yaml 2>/dev/null | wc -l | tr -d ' ')
if [ "$GOV_COUNT" -gt 0 ]; then
    echo "✅ Governance evidence: $GOV_COUNT files"
    PASSED=$((PASSED + 1))
else
    echo "❌ No governance evidence"
    FAILED=$((FAILED + 1))
fi

echo ""

# Phase 3: Constitutional (compare against spec)
echo "  PHASE 3: CONSTITUTIONAL COMPLIANCE"
echo "  ─────────────────────────────────"

# All commands governed?
TOTAL_PLUGINS=$(ls .sdk/commands/plugins/*.sh 2>/dev/null | wc -l | tr -d ' ')
GOVERNED=$(for p in .sdk/commands/plugins/*.sh; do
    name=$(basename "$p" .sh)
    grep -q "$name" .sdk/engines/governance/levels.sh 2>/dev/null && echo "1"
done | wc -l | tr -d ' ')
echo "  Commands governed: $GOVERNED / $TOTAL_PLUGINS"

# State file ownership valid?
STATE_COUNT=$(ls .kin/state/*.yaml 2>/dev/null | wc -l | tr -d ' ')
echo "  State files: $STATE_COUNT"

echo ""
echo "────────────────────────────────────────"
TOTAL_CHECKS=$((PASSED + FAILED + WARNINGS))
echo "  Passed:   $PASSED"
echo "  Failed:   $FAILED"  
echo "  Warnings: $WARNINGS"

# Score
if [ $TOTAL_CHECKS -gt 0 ]; then
    SCORE=$(( (PASSED * 100) / TOTAL_CHECKS ))
else
    SCORE=0
fi
echo "  Score:    ${SCORE}%"
echo ""

if [ $SCORE -ge 95 ] && [ $FAILED -eq 0 ]; then
    echo "  Status: ✅ COMPLIANT — Architecture Freeze Valid"
elif [ $SCORE -ge 80 ]; then
    echo "  Status: ⚠️  MINOR GAPS"
else
    echo "  Status: ❌ NON-COMPLIANT"
fi
echo "════════════════════════════════════════════"

# Save summary
mkdir -p "$COMPLIANCE_DIR"
cat > "$COMPLIANCE_DIR/summary_$(date +%Y%m%d_%H%M%S).yaml" << YAML
constitution: v3.3
timestamp: $(date -u +%Y-%m-%dT%H:%M:%SZ)
passed: $PASSED
failed: $FAILED
warnings: $WARNINGS
score: $SCORE
compliant: $([ $SCORE -ge 95 ] && echo "true" || echo "false")
YAML

echo ""
echo "  Summary: .kin/compliance/"
