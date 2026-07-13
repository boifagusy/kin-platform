#!/data/data/com.termux/files/usr/bin/bash
# Article 2: Enforcement Model

source .sdk/engines/compliance/helpers.sh 2>/dev/null

article_02_structural() {
    local pass=true
    [ -f ".sdk/engines/governance/engine.sh" ] || pass=false
    [ -f ".sdk/engines/gate/engine.sh" ] || pass=false
    [ -f ".sdk/commands/plugins/guard.sh" ] || pass=false
    $pass && compliance_test "article_02" "Enforcement files exist" "structural" "PASS" "" \
           || compliance_test "article_02" "Enforcement files exist" "structural" "FAIL" "Missing enforcement engine"
}

article_02_behavioral() {
    # Actually test that guard blocks when it should
    local result=$(ai guard implement 2>&1 | grep -c "BLOCKED\|too early")
    if [ "$result" -gt 0 ]; then
        compliance_test "article_02" "Guard blocks implementation at wrong gate" "behavioral" "PASS" ""
    else
        compliance_test "article_02" "Guard blocks implementation at wrong gate" "behavioral" "WARN" "Guard may not be blocking"
    fi
}

article_02_structural
article_02_behavioral
