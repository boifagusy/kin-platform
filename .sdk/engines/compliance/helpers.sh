#!/data/data/com.termux/files/usr/bin/bash

COMPLIANCE_DIR=".kin/compliance"
CONSTITUTION="ENGINEERING_OS_CONSTITUTION.md"

# Standard test result
compliance_test() {
    local article="$1" name="$2" phase="$3" result="$4" detail="$5"
    local now=$(date -u +%Y-%m-%dT%H:%M:%SZ)
    
    case "$result" in
        PASS) echo "  ✅ $name" ;;
        FAIL) echo "  ❌ $name — $detail" ;;
        WARN) echo "  ⚠️  $name — $detail" ;;
    esac
    
    # Save article evidence
    mkdir -p "$COMPLIANCE_DIR/articles"
    cat > "$COMPLIANCE_DIR/articles/${article}.yaml" << YAML
article: $article
name: $name
phase: $phase
result: $result
detail: $detail
timestamp: $now
YAML
    
    echo "$result"
}

# Run a command and capture exit code
compliance_exec() {
    local cmd="$1"
    bash -c "$cmd" 2>/dev/null
    echo $?
}
