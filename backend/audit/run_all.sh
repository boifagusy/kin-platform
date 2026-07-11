#!/data/data/com.termux/files/usr/bin/bash
# KIN Recovery Suite - Read-Only Audit Master

set -euo pipefail

AUDIT_DIR="$(cd "$(dirname "$0")" && pwd)"
BACKEND_DIR="$(dirname "$AUDIT_DIR")"
TIMESTAMP=$(date '+%Y%m%d_%H%M%S')
REPORT_DIR="$AUDIT_DIR/reports/audit_$TIMESTAMP"
TEMP_DIR="$REPORT_DIR/temp"

mkdir -p "$REPORT_DIR" "$TEMP_DIR"

# Load framework
source "$AUDIT_DIR/framework.sh"

# Safety check
READONLY_CHECK

echo "=========================================="
echo "KIN RECOVERY AUDIT - READ ONLY"
echo "Timestamp: $TIMESTAMP"
echo "Reports: $REPORT_DIR"
echo "=========================================="

# Generate fingerprint
FINGERPRINT "$REPORT_DIR"

# Run all phases
PHASES=(
    "01_environment"
    "02_git_recovery"
    "03_backend_inventory"
    "04_frontend_audit"
    "05_dependency_graph"
    "06_api_contract"
    "07_database_schema"
    "08_backup_compare"
    "09_route_validation"
    "10_feature_scoring"
    "11_timeline_reconstruction"
    "12_executive_summary"
)

for phase in "${PHASES[@]}"; do
    echo ""
    echo "=== $phase ==="
    
    PHASE_SCRIPT="$AUDIT_DIR/${phase}.sh"
    if [ -f "$PHASE_SCRIPT" ]; then
        bash "$PHASE_SCRIPT" "$BACKEND_DIR" "$REPORT_DIR" "$TEMP_DIR" 2>&1 | tee "$REPORT_DIR/${phase}.log"
        echo "✓ $phase complete"
    else
        echo "⚠ $phase script not found, skipping"
    fi
done

echo ""
echo "=========================================="
echo "AUDIT COMPLETE"
echo "=========================================="
echo "Reports: $REPORT_DIR"
echo ""
echo "Files generated:"
find "$REPORT_DIR" -type f -not -path "*/temp/*" | sort | while read f; do
    echo "  $(basename "$f")"
done

