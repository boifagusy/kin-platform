#!/data/data/com.termux/files/usr/bin/bash
# Article 9: Certification Standard

source .sdk/engines/compliance/helpers.sh 2>/dev/null

article_09_structural() {
    [ -f ".sdk/engines/certify/engine.sh" ] && [ -d ".kin/certifications" ] \
        && compliance_test "article_09" "Certification engine + evidence dir" "structural" "PASS" "" \
        || compliance_test "article_09" "Certification engine + evidence dir" "structural" "FAIL" "Missing"
}

article_09_behavioral() {
    local cert_count=$(ls .kin/certifications/*.yaml 2>/dev/null | wc -l | tr -d ' ')
    if [ "$cert_count" -gt 0 ]; then
        compliance_test "article_09" "Certification evidence ($cert_count records)" "behavioral" "PASS" ""
    else
        compliance_test "article_09" "Certification evidence" "behavioral" "WARN" "No certification records"
    fi
}

article_09_structural
article_09_behavioral
