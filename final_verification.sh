#!/data/data/com.termux/files/usr/bin/bash

echo "🔒 HARDENED SDK VERIFICATION"
echo "════════════════════════════════"
echo ""

PASS=0
FAIL=0

# Test 1: All rules present
echo "1. Termux Enforcement Rules"
RULES=$(grep -c "^## RULE" docs/governance/TERMUX_ENFORCEMENT.md)
if [ "$RULES" -ge 12 ]; then
    echo "   ✅ $RULES rules defined (12 required)"
    PASS=$((PASS + 1))
else
    echo "   ❌ Only $RULES rules (12 required)"
    FAIL=$((FAIL + 1))
fi

# Test 2: Single block enforcement
echo ""
echo "2. Contract Enforcement"
if grep -q "single_executable_block" docs/governance/AI_CONTRACT.yaml; then
    echo "   ✅ Single block enforced"
    PASS=$((PASS + 1))
else
    echo "   ❌ Single block not enforced"
    FAIL=$((FAIL + 1))
fi

# Test 3: No truncation rule
echo ""
echo "3. Anti-Truncation Rules"
if grep -q "NO TRUNCATED FILES" docs/governance/TERMUX_ENFORCEMENT.md; then
    echo "   ✅ Truncation forbidden"
    PASS=$((PASS + 1))
else
    echo "   ❌ Truncation not addressed"
    FAIL=$((FAIL + 1))
fi

# Test 4: Installer exists
echo ""
echo "4. SDK Installer"
if [ -x ".sdk/commands/plugins/installer.sh" ]; then
    echo "   ✅ Installer ready"
    PASS=$((PASS + 1))
else
    echo "   ❌ Installer missing"
    FAIL=$((FAIL + 1))
fi

# Test 5: Doctor works
echo ""
echo "5. Doctor Diagnostic"
if ./.sdk/commands/ai doctor > /dev/null 2>&1; then
    echo "   ✅ Doctor operational"
    PASS=$((PASS + 1))
else
    echo "   ❌ Doctor failed"
    FAIL=$((FAIL + 1))
fi

# Test 6: All plugins executable
echo ""
echo "6. Plugin Executability"
PLUGINS=$(find .sdk/commands/plugins -name "*.sh" -executable | wc -l)
TOTAL=$(find .sdk/commands/plugins -name "*.sh" | wc -l)
if [ "$PLUGINS" -eq "$TOTAL" ]; then
    echo "   ✅ All $TOTAL plugins executable"
    PASS=$((PASS + 1))
else
    echo "   ❌ $PLUGINS/$TOTAL plugins executable"
    FAIL=$((FAIL + 1))
fi

# Summary
echo ""
echo "════════════════════════════════"
echo "Results: $PASS passed, $FAIL failed"
echo ""

if [ $FAIL -eq 0 ]; then
    echo "✅ SDK HARDENED AND READY"
    echo ""
    echo "Install into another project:"
    echo "  cd /path/to/project"
    echo "  git init  # if needed"
    echo "  /path/to/kin_project/.sdk/commands/ai install"
else
    echo "❌ $FAIL issues need attention"
fi
