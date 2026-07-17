# Description: Engine API compliance checker
engine_main() {
    local action="${1:-compliance}"
    
    case "$action" in
        compliance)
            echo ""
            echo "ENGINE API COMPLIANCE"
            echo "═══════════════════════════════════════"
            echo ""
            
            local compliant=0 total=0
            
            for dir in .sdk/engines/*/; do
                [ -d "$dir" ] || continue
                local name=$(basename "$dir")
                total=$((total + 1))
                
                # Check for API dispatch pattern in ANY file in the engine directory
                local has_api=false
                find "$dir" -name "*.sh" -type f 2>/dev/null | while read f; do
                    grep -q "api)" "$f" 2>/dev/null && has_api=true
                done
                
                # Also check for health/version/doctor functions
                grep -rq "${name}_health\|${name}_version\|${name}_doctor\|${name}_validate" "$dir" 2>/dev/null && has_api=true
                
                if $has_api; then
                    echo "  ✅ $name"
                    compliant=$((compliant + 1))
                else
                    echo "  ⏳ $name — pending"
                fi
            done 2>/dev/null
            
            # Count by checking files directly
            echo ""
            echo "────────────────────────────────────────"
            
            local actual=0
            for dir in .sdk/engines/*/; do
                name=$(basename "$dir")
                if grep -rq "api)" "$dir" 2>/dev/null || grep -rq "${name}_health\|Standard Engine API" "$dir" 2>/dev/null; then
                    actual=$((actual + 1))
                fi
            done
            
            echo "  Compliance: $actual / $total"
            echo "  Target:     $total / $total"
            echo "  Reference:  gate, intelligence, project"
            echo "═══════════════════════════════════════"
            ;;
        *)
            echo "Usage: ai engine compliance"
            ;;
    esac
}
main() { engine_main "$@"; }
