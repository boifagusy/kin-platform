# Description: Explain concepts
explain_main() {
    local target="$1"
    local names=("Bootstrap" "Discovery" "Requirements" "Architecture" "Dependency Planning" "Brick Planning" "Brick Development" "Brick Testing" "Integration Testing" "System Testing" "Production Validation" "Release")
    
    if [ -d "bricks/$target" ] 2>/dev/null; then
        echo "BRICK: $target"
        grep -q "status:" "bricks/$target/brick.yaml" 2>/dev/null && echo "Status: $(grep "status:" "bricks/$target/brick.yaml" | sed 's/.*: //')"
        echo "Path: bricks/$target/"
    elif echo "$target" | grep -qE '^[0-9]+$' && [ "$target" -ge 0 ] && [ "$target" -le 11 ]; then
        echo "Gate $target: ${names[$target]}"
        [ "$target" -lt 11 ] && echo "Next: Gate $((target + 1)) — ${names[$((target + 1))]}"
    else
        echo "Usage: ai explain <0-11> | ai explain <brick-name>"
    fi
}
main() { explain_main "$@"; }
