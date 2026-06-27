#!/bin/bash

cd ~/storage/kin_platform/frontend

echo "=========================================="
echo "  FRONTEND VERIFICATION"
echo "=========================================="

PASS=0
FAIL=0

# Test 1: Build
echo ""
echo "Test 1: npm run build"
if npm run build > /dev/null 2>&1; then
    echo "✅ Build passed"
    PASS=$((PASS+1))
else
    echo "❌ Build failed"
    FAIL=$((FAIL+1))
fi

# Test 2: dist exists
echo ""
echo "Test 2: dist/ exists"
if [ -d "dist" ]; then
    echo "✅ dist/ exists"
    PASS=$((PASS+1))
else
    echo "❌ dist/ missing"
    FAIL=$((FAIL+1))
fi

# Test 3: index.html exists
echo ""
echo "Test 3: index.html exists"
if [ -f "dist/index.html" ]; then
    echo "✅ index.html exists"
    PASS=$((PASS+1))
else
    echo "❌ index.html missing"
    FAIL=$((FAIL+1))
fi

# Test 4: assets exist
echo ""
echo "Test 4: assets/ exists"
if [ -d "dist/assets" ]; then
    echo "✅ assets/ exists"
    PASS=$((PASS+1))
else
    echo "❌ assets/ missing"
    FAIL=$((FAIL+1))
fi

# Test 5: capacitor config exists
echo ""
echo "Test 5: capacitor.config.json exists"
if [ -f "capacitor.config.json" ]; then
    echo "✅ capacitor.config.json exists"
    PASS=$((PASS+1))
else
    echo "❌ capacitor.config.json missing"
    FAIL=$((FAIL+1))
fi

# Test 6: android exists
echo ""
echo "Test 6: android/ exists"
if [ -d "android" ]; then
    echo "✅ android/ exists"
    PASS=$((PASS+1))
else
    echo "❌ android/ missing"
    FAIL=$((FAIL+1))
fi

# Test 7: package.json has all dependencies
echo ""
echo "Test 7: Checking dependencies"
REQUIRED_DEPS=("react" "react-dom" "react-router-dom" "@capacitor/core" "@capacitor/android")
for dep in "${REQUIRED_DEPS[@]}"; do
    if grep -q "\"$dep\"" package.json; then
        echo "✅ $dep found"
        PASS=$((PASS+1))
    else
        echo "❌ $dep missing"
        FAIL=$((FAIL+1))
    fi
done

# Summary
echo ""
echo "=========================================="
echo "  VERIFICATION SUMMARY"
echo "=========================================="
echo "✅ Pass: $PASS"
echo "❌ Fail: $FAIL"

if [ $FAIL -eq 0 ]; then
    echo ""
    echo "🎉 ALL TESTS PASSED! Frontend is production ready."
else
    echo ""
    echo "⚠️ $FAIL test(s) failed. Check the output above."
fi
