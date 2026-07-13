#!/data/data/com.termux/files/usr/bin/bash
# Article 17: Evidence Requirements

source .sdk/engines/compliance/helpers.sh 2>/dev/null

article_17_structural() {
    local pass=true
    [ -d ".kin/evidence" ] || pass=false
    [ -d ".kin/contracts" ] || pass=false
    [ -d ".kin/certifications" ] || pass=false
    $pass && compliance_test "article_17" "Evidence directories exist" "structural" "PASS" "" \
           || compliance_test "article_17" "Evidence directories exist" "structural" "FAIL" "Missing"
}

article_17_behavioral() {
    local evidence_total=0
    evidence_total=$(( $(ls .kin/evidence/*.yaml 2>/dev/null | wc -l | tr -d ' ') ))
    evidence_total=$(( evidence_total + $(ls .kin/contracts/*.json 2>/dev/null | wc -l | tr -d ' ') ))
    evidence_total=$(( evidence_total + $(ls .kin/certifications/*.yaml 2>/dev/null | wc -l | tr -d ' ') ))
    
    if [ "$evidence_total" -gt 10 ]; then
        compliance_test "article_17" "Evidence produced ($evidence_total files)" "behavioral" "PASS" ""
    else
        compliance_test "article_17" "Evidence produced" "behavioral" "FAIL" "Only $evidence_total evidence files"
    fi
}

article_17_structural
article_17_behavioral
