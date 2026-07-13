#!/data/data/com.termux/files/usr/bin/bash
# Article 3: Core Principles

source .sdk/engines/compliance/helpers.sh 2>/dev/null

article_03_structural() {
    local pass=true
    [ -f ".sdk/engines/REGISTRY.yaml" ] || pass=false
    [ -f ".sdk/engines/investigate/engine.sh" ] || pass=false
    [ -f ".sdk/engines/contracts/engine.sh" ] || pass=false
    $pass && compliance_test "article_03" "Core principle engines exist" "structural" "PASS" "" \
           || compliance_test "article_03" "Core principle engines exist" "structural" "FAIL" "Missing engine"
}

article_03_behavioral() {
    # Verify investigation produces evidence
    if [ -d ".kin/investigations" ] && [ -n "$(ls -A .kin/investigations 2>/dev/null)" ]; then
        compliance_test "article_03" "Investigation evidence exists" "behavioral" "PASS" ""
    else
        compliance_test "article_03" "Investigation evidence exists" "behavioral" "WARN" "No investigation evidence"
    fi
}

article_03_structural
article_03_behavioral
