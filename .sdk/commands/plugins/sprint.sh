# Description: Sprint management — start, close, status
sprint_main() {
    local action="${1:-status}"
    local sprint_dir=".kin/sprints"
    mkdir -p "$sprint_dir"
    
    case "$action" in
        start)
            local name="${2:-Sprint $(date +%Y%m%d)}"
            cat > "$sprint_dir/current.yaml" << YAML
sprint:
  name: $name
  started: $(date -u +%Y-%m-%dT%H:%M:%SZ)
  status: active
  tasks_completed: 0
  bricks_completed: 0
YAML
            echo "🏃 Sprint started: $name"
            ;;
        close)
            if [ -f "$sprint_dir/current.yaml" ]; then
                local name=$(grep "name:" "$sprint_dir/current.yaml" | sed 's/.*: //')
                sed -i 's/active/completed/' "$sprint_dir/current.yaml"
                echo "completed: $(date -u +%Y-%m-%dT%H:%M:%SZ)" >> "$sprint_dir/current.yaml"
                
                # Archive
                local archive="$sprint_dir/archive"
                mkdir -p "$archive"
                cp "$sprint_dir/current.yaml" "$archive/sprint_$(date +%Y%m%d_%H%M%S).yaml"
                
                echo "✅ Sprint closed: $name"
                echo "   Archived to $archive"
            else
                echo "No active sprint"
            fi
            ;;
        status)
            if [ -f "$sprint_dir/current.yaml" ]; then
                cat "$sprint_dir/current.yaml"
            else
                echo "No active sprint. Start: ai sprint start"
            fi
            ;;
        *)
            echo "Usage: ai sprint [start|close|status]"
            ;;
    esac
}
main() { sprint_main "$@"; }
