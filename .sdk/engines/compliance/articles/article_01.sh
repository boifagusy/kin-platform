#!/data/data/com.termux/files/usr/bin/bash
# Article 1: Authority Hierarchy

source .sdk/engines/compliance/helpers.sh 2>/dev/null

article_01_structural() {
    local pass=true
    [ -f "ENGINEERING_OS_CONSTITUTION.md" ] || pass=false
    [ -d "docs/adr" ] || pass=false
    [ -f ".sdk/engines/REGISTRY.yaml" ] || pass=false
    [ -d ".sdk/engines" ] || pass=false
    [ -d ".sdk/commands/plugins" ] || pass=false
    $pass && compliance_test "article_01" "Authority Hierarchy" "structural" "PASS" "" \
           || compliance_test "article_01" "Authority Hierarchy" "structural" "FAIL" "Missing component"
}

article_01_behavioral() {
    # Verify Constitution is actually read by OS
    if grep -q "CONSTITUTION" .sdk/engines/compliance/engine.sh 2>/dev/null; then
        compliance_test "article_01" "Constitution referenced in code" "behavioral" "PASS" ""
    else
        compliance_test "article_01" "Constitution referenced in code" "behavioral" "WARN" "Not referenced in engine code"
    fi
}

article_01_structural
article_01_behavioral
