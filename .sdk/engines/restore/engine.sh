#!/data/data/com.termux/files/usr/bin/bash

RESTORE_DIR=".kin/restore"
RECIPES_DIR="$RESTORE_DIR/recipes"
HISTORY_DIR="$RESTORE_DIR/history"
mkdir -p "$RECIPES_DIR" "$HISTORY_DIR"

restore_help() {
    echo "Usage: ai restore [list|report|verify|run <recipe>]"
    echo ""
    echo "  ai restore list              Available recipes"
    echo "  ai restore report            Restoration history"
    echo "  ai restore verify            Verify components"
    echo "  ai restore run <recipe>      Execute recipe"
    echo "  ai restore run <r> --dry-run Preview actions"
    echo ""
    restore_list
}

restore_list() {
    echo "AVAILABLE RESTORE RECIPES"
    echo "═══════════════════════════════════════"
    if [ -d "$RECIPES_DIR" ] && [ -n "$(ls -A "$RECIPES_DIR" 2>/dev/null)" ]; then
        for recipe in "$RECIPES_DIR"/*.sh; do
            [ -f "$recipe" ] || continue
            local name desc
            name="$(basename "$recipe" .sh)"
            desc="$(head -1 "$recipe" 2>/dev/null | sed 's/^# //')"
            echo "  $name — ${desc:-No description}"
        done
    else
        echo "  (no recipes)"
    fi
}

restore_report() {
    echo "RESTORE HISTORY"
    echo "═══════════════════════════════════════"
    if [ -d "$HISTORY_DIR" ] && [ -n "$(ls -A "$HISTORY_DIR" 2>/dev/null)" ]; then
        ls -1t "$HISTORY_DIR"/*.log 2>/dev/null | head -20 | while read f; do
            echo "  $(basename "$f" .log)"
        done
    else
        echo "  (no history)"
    fi
}

restore_verify() {
    echo "VERIFYING COMPONENTS"
    echo "═══════════════════════════════════════"
    [ -d ".sdk" ] && echo "  ✅ SDK" || echo "  ❌ SDK missing"
    [ -d ".kin/state" ] && echo "  ✅ State" || echo "  ❌ State missing"
    [ -d "docs/governance" ] && echo "  ✅ Governance" || echo "  ❌ Governance missing"
    echo "  Engines: $(ls -1d .sdk/engines/*/ 2>/dev/null | wc -l | tr -d ' ') found"
}

restore_run() {
    local recipe="$1"
    local dry=false
    for arg in "$@"; do [ "$arg" = "--dry-run" ] && dry=true; done
    
    local recipe_file="$RECIPES_DIR/${recipe}.sh"
    if [ ! -f "$recipe_file" ]; then
        echo "Recipe not found: $recipe"
        restore_list
        return 1
    fi
    
    echo "RESTORE: $recipe"
    echo "═══════════════════════════════════════"
    
    if $dry; then
        echo "  DRY RUN — Preview:"
        grep "^#" "$recipe_file" 2>/dev/null | head -5
        echo "  (no changes made)"
        return 0
    fi
    
    echo "  Executing..."
    bash "$recipe_file" 2>/dev/null && echo "  ✅ Complete" || echo "  ❌ Failed"
    echo "$(date) — $recipe" > "$HISTORY_DIR/${recipe}_$(date +%Y%m%d_%H%M%S).log"
}

# Clean dispatch
case "$1" in
    list)    restore_list ;;
    report)  restore_report ;;
    verify)  restore_verify ;;
    run)     shift; restore_run "$@" ;;
    help|"") restore_help ;;
    *)       restore_run "$@" ;;  # Treat unknown as recipe name
esac
