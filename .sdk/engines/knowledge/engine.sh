#!/data/data/com.termux/files/usr/bin/bash

if [ -n "$SDK_ROOT" ]; then
    KERNEL_DIR="$SDK_ROOT/kernel"
    ENGINES_DIR="$SDK_ROOT/engines"
else
    SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    ENGINES_DIR="$(dirname "$SCRIPT_DIR")"
    KERNEL_DIR="$(dirname "$ENGINES_DIR")/kernel"
fi

source "$KERNEL_DIR/common.sh" 2>/dev/null || true
source "$KERNEL_DIR/logger.sh" 2>/dev/null || true
source "$KERNEL_DIR/filesystem.sh" 2>/dev/null || true
source "$KERNEL_DIR/yaml.sh" 2>/dev/null || true

# Get knowledge directory
get_knowledge_dir() {
    local root
    root="$(get_project_root 2>/dev/null)" || root="$HOME"
    echo "$root/.kin/knowledge"
}

# Add a known bug
knowledge_add_bug() {
    local symptom="$1"
    local root_cause="$2"
    local fix="$3"
    local bricks="${4:-unknown}"
    local severity="${5:-medium}"
    local now
    now="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
    
    local kdir
    kdir="$(get_knowledge_dir)/bugs"
    ensure_dir "$kdir"
    
    # Generate ID
    local bug_id="BUG-$(date +%s)"
    local bug_file="$kdir/${bug_id}.yaml"
    
    # Check for duplicates
    if grep -r "$symptom" "$kdir"/*.yaml 2>/dev/null | grep -q "."; then
        echo "Similar bug already exists"
        return 1
    fi
    
    cat > "$bug_file" <<YAML
bug:
  id: $bug_id
  symptom: $symptom
  root_cause: $root_cause
  fix: $fix
  bricks: $bricks
  severity: $severity
  discovered: $now
  occurrences: 1
  resolved: false
YAML
    
    log_info "Bug recorded: $bug_id"
    echo "Bug recorded: $bug_id"
}

# Add a pattern
knowledge_add_pattern() {
    local name="$1"
    local description="$2"
    local when_to_use="$3"
    local now
    now="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
    
    local kdir
    kdir="$(get_knowledge_dir)/patterns"
    ensure_dir "$kdir"
    
    local pattern_id="PAT-$(date +%s)"
    local pattern_file="$kdir/${pattern_id}.yaml"
    
    # Check duplicates
    if grep -r "$name" "$kdir"/*.yaml 2>/dev/null | grep -q "."; then
        echo "Similar pattern already exists"
        return 1
    fi
    
    cat > "$pattern_file" <<YAML
pattern:
  id: $pattern_id
  name: $name
  description: $description
  when_to_use: $when_to_use
  added: $now
  used_count: 0
YAML
    
    log_info "Pattern recorded: $pattern_id"
    echo "Pattern recorded: $pattern_id"
}

# Add a lesson learned
knowledge_add_lesson() {
    local context="$1"
    local finding="$2"
    local action="$3"
    local now
    now="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
    
    local kdir
    kdir="$(get_knowledge_dir)/lessons"
    ensure_dir "$kdir"
    
    local lesson_id="LESSON-$(date +%s)"
    local lesson_file="$kdir/${lesson_id}.yaml"
    
    cat > "$lesson_file" <<YAML
lesson:
  id: $lesson_id
  context: $context
  finding: $finding
  action: $action
  date: $now
YAML
    
    log_info "Lesson recorded: $lesson_id"
    echo "Lesson recorded: $lesson_id"
}

# Search knowledge base
knowledge_search() {
    local query="$1"
    local kdir
    kdir="$(get_knowledge_dir)"
    
    echo "Knowledge search: $query"
    echo "═══════════════════════════════════════"
    
    local found=0
    
    # Search bugs
    if [ -d "$kdir/bugs" ]; then
        for f in "$kdir/bugs"/*.yaml; do
            [ -f "$f" ] || continue
            if grep -qi "$query" "$f" 2>/dev/null; then
                local id symptom fix
                id="$(yaml_get_nested "$f" "bug" "id" 2>/dev/null)"
                symptom="$(yaml_get_nested "$f" "bug" "symptom" 2>/dev/null)"
                fix="$(yaml_get_nested "$f" "bug" "fix" 2>/dev/null)"
                echo "  🐛 $id: $symptom"
                echo "     Fix: $fix"
                found=$((found + 1))
            fi
        done
    fi
    
    # Search patterns
    if [ -d "$kdir/patterns" ]; then
        for f in "$kdir/patterns"/*.yaml; do
            [ -f "$f" ] || continue
            if grep -qi "$query" "$f" 2>/dev/null; then
                local id name desc
                id="$(yaml_get_nested "$f" "pattern" "id" 2>/dev/null)"
                name="$(yaml_get_nested "$f" "pattern" "name" 2>/dev/null)"
                desc="$(yaml_get_nested "$f" "pattern" "description" 2>/dev/null)"
                echo "  📐 $id: $name - $desc"
                found=$((found + 1))
            fi
        done
    fi
    
    # Search lessons
    if [ -d "$kdir/lessons" ]; then
        for f in "$kdir/lessons"/*.yaml; do
            [ -f "$f" ] || continue
            if grep -qi "$query" "$f" 2>/dev/null; then
                local id finding
                id="$(yaml_get_nested "$f" "lesson" "id" 2>/dev/null)"
                finding="$(yaml_get_nested "$f" "lesson" "finding" 2>/dev/null)"
                echo "  📝 $id: $finding"
                found=$((found + 1))
            fi
        done
    fi
    
    [ $found -eq 0 ] && echo "  (no results)"
    echo "  Found: $found results"
}

# List all knowledge
knowledge_list() {
    local kdir type="${1:-all}"
    kdir="$(get_knowledge_dir)"
    
    echo "KNOWLEDGE BASE"
    echo "═══════════════════════════════════════"
    
    if [ "$type" = "all" ] || [ "$type" = "bugs" ]; then
        echo "Bugs:"
        if [ -d "$kdir/bugs" ]; then
            local count=0
            for f in "$kdir/bugs"/*.yaml; do
                [ -f "$f" ] || continue
                local id symptom
                id="$(yaml_get_nested "$f" "bug" "id" 2>/dev/null)"
                symptom="$(yaml_get_nested "$f" "bug" "symptom" 2>/dev/null)"
                echo "  $id: $symptom"
                count=$((count + 1))
            done
            [ $count -eq 0 ] && echo "  (none)"
        fi
    fi
    
    if [ "$type" = "all" ] || [ "$type" = "patterns" ]; then
        echo "Patterns:"
        if [ -d "$kdir/patterns" ]; then
            local count=0
            for f in "$kdir/patterns"/*.yaml; do
                [ -f "$f" ] || continue
                local name
                name="$(yaml_get_nested "$f" "pattern" "name" 2>/dev/null)"
                echo "  $name"
                count=$((count + 1))
            done
            [ $count -eq 0 ] && echo "  (none)"
        fi
    fi
    
    if [ "$type" = "all" ] || [ "$type" = "lessons" ]; then
        echo "Lessons:"
        if [ -d "$kdir/lessons" ]; then
            local count=0
            for f in "$kdir/lessons"/*.yaml; do
                [ -f "$f" ] || continue
                local finding
                finding="$(yaml_get_nested "$f" "lesson" "finding" 2>/dev/null)"
                echo "  $finding"
                count=$((count + 1))
            done
            [ $count -eq 0 ] && echo "  (none)"
        fi
    fi
}

# Count knowledge entries
knowledge_count() {
    local kdir
    kdir="$(get_knowledge_dir)"
    local total=0
    for sub in bugs patterns lessons recipes; do
        if [ -d "$kdir/$sub" ]; then
            local c
            c=$(ls -1 "$kdir/$sub"/*.yaml 2>/dev/null | wc -l | tr -d ' ')
            total=$((total + c))
        fi
    done
    echo "$total"
}

# Suggest relevant knowledge based on context
knowledge_suggest() {
    local context="${1:-}"
    local kdir
    kdir="$(get_knowledge_dir)"
    
    if [ -n "$context" ]; then
        echo "Suggestions for: $context"
        knowledge_search "$context"
    else
        # Suggest based on current gate/brick
        local gate brick
        gate="$(state_read "gate.yaml" "current" 2>/dev/null | tr -d ' ')"
        brick="$(state_read "brick.yaml" "active_brick" 2>/dev/null | tr -d ' ')"
        echo "Suggestions for Gate $gate, Brick: ${brick:-none}"
        if [ -n "$brick" ]; then
            knowledge_search "$brick"
        fi
    fi
}
