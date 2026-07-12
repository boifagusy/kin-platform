#!/data/data/com.termux/files/usr/bin/bash

# ENGINEERING OS DOCTOR — Complete health check
# First command every AI should run

if [ -n "$SDK_ROOT" ]; then
    ENGINES_DIR="$SDK_ROOT/engines"
else
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    ENGINES_DIR="$(dirname "$SCRIPT_DIR")"
fi

echo ""
echo "════════════════════════════════════════════"
echo "  ENGINEERING OS — DOCTOR"
echo "════════════════════════════════════════════"
echo ""

# ── OS Health ──
echo "  ENGINEERING OS"
echo "  ─────────────────────────────────"

# Read from authoritative registry
if [ -f "$ENGINES_DIR/REGISTRY.yaml" ]; then
    expected=$(grep -c "id:" "$ENGINES_DIR/REGISTRY.yaml" 2>/dev/null)
    actual=$(ls -1d "$ENGINES_DIR"/*/ 2>/dev/null | wc -l | tr -d ' ')
    echo "  Registry:    ✅ Authoritative ($expected engines defined)"
    echo "  Directories: $actual found ($((actual - expected)) beyond registry)"
else
    echo "  Registry:    ❌ Missing"
fi

# Architecture freeze
if [ -f ".sdk/ARCHITECTURE_FREEZE.md" ]; then
    echo "  Freeze:      ✅ Active (v3.3)"
else
    echo "  Freeze:      ⚠️ Not active"
fi

# State
if [ -f ".kin/state/session.yaml" ]; then
    echo "  State:       ✅ Healthy"
else
    echo "  State:       ⚠️ Needs session start"
fi

# ── Project Health ──
echo ""
echo "  PROJECT"
echo "  ─────────────────────────────────"

local services contracts tests certs
services=$(find backend/app/Services -name "*.php" -type f 2>/dev/null | wc -l | tr -d ' ')
contracts=$(find .sdk/contracts -name "*.yaml" -type f 2>/dev/null | wc -l | tr -d ' ')
tests=$(find backend/tests -name "*Test.php" -type f 2>/dev/null | wc -l | tr -d ' ')
certs=$(ls -1 .kin/certifications/*.yaml 2>/dev/null | wc -l | tr -d ' ')

local contract_pct=0 test_pct=0
[ "$services" -gt 0 ] && contract_pct=$((contracts * 100 / services))
[ "$services" -gt 0 ] && test_pct=$((tests * 100 / services))

echo "  Services:    $services"
echo "  Contracts:   ${contract_pct}% (${contracts}/${services})"
echo "  Tests:       ${test_pct}% (${tests}/${services})"
echo "  Certified:   $certs tasks"

# Risk assessment
local risk="LOW"
[ "$contract_pct" -lt 30 ] && risk="MEDIUM"
[ "$contract_pct" -lt 10 ] && risk="HIGH"
[ "$test_pct" -lt 20 ] && risk="HIGH"

echo "  Risk:        $risk"

# ── Recommendation ──
echo ""
echo "  RECOMMENDATION"
echo "  ─────────────────────────────────"

if [ "$contract_pct" -lt 30 ]; then
    echo "  ▶ Contracts critical — ai contract verify"
elif [ "$test_pct" -lt 30 ]; then
    echo "  ▶ Tests needed — ai investigate services"
elif [ "$certs" -eq 0 ]; then
    echo "  ▶ Certify completed work — ai certify create"
else
    echo "  ▶ Ready for development — ai workflow next"
fi

echo ""
echo "════════════════════════════════════════════"
