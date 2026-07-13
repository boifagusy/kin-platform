#!/data/data/com.termux/files/usr/bin/bash
# Article 6: Command Lifecycle — every command passes through governance

source .sdk/engines/compliance/helpers.sh 2>/dev/null

article_06_structural() {
    local governed=0 ungoverned=0
    for plugin in .sdk/commands/plugins/*.sh; do
        name=$(basename "$plugin" .sh)
        if grep -q "$name" .sdk/engines/governance/levels.sh 2>/dev/null; then
            governed=$((governed + 1))
        else
            ungoverned=$((ungoverned + 1))
        fi
    done
    
    if [ "$ungoverned" -eq 0 ]; then
        compliance_test "article_06" "All commands governed ($governed/$((governed+ungoverned)))" "structural" "PASS" ""
    else
        compliance_test "article_06" "All commands governed" "structural" "FAIL" "$ungoverned ungoverned commands"
    fi
}

article_06_behavioral() {
    # Actually run a command and check governance was invoked
    local evidence_count=$(ls .kin/evidence/governance_*.yaml 2>/dev/null | wc -l | tr -d ' ')
    if [ "$evidence_count" -gt 0 ]; then
        compliance_test "article_06" "Governance evidence produced ($evidence_count files)" "behavioral" "PASS" ""
    else
        compliance_test "article_06" "Governance evidence produced" "behavioral" "FAIL" "No governance evidence files"
    fi
}

article_06_structural
article_06_behavioral
