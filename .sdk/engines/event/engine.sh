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
source "$ENGINES_DIR/event/definitions.sh" 2>/dev/null || true

# Get events directory
get_events_dir() {
    local root
    root="$(get_project_root 2>/dev/null)" || root="$HOME"
    echo "$root/.kin/events"
}

# Get subscribers directory
get_subscribers_dir() {
    local sdk_root
    [ -n "$SDK_ROOT" ] && echo "$SDK_ROOT/engines/event/subscribers" || {
        SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
        echo "$SCRIPT_DIR/subscribers"
    }
}

# Publish an event
event_publish() {
    local type="$1"
    local source="$2"
    local data="${3:-}"
    local now
    now="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
    local event_id="evt_$(date +%s)_$$"
    
    # Validate event type
    if ! event_type_valid "$type"; then
        log_error "Invalid event type: $type"
        return 1
    fi
    
    # Create event record
    local events_dir
    events_dir="$(get_events_dir)"
    ensure_dir "$events_dir"
    
    local event_file="$events_dir/${event_id}.yaml"
    
    cat > "$event_file" <<YAML
event:
  id: $event_id
  type: $type
  source: $source
  timestamp: $now
  data: $data
YAML
    
    log_debug "Event published: $type by $source"
    
    # Notify subscribers
    event_notify "$type" "$event_file"
    
    echo "$event_id"
    return 0
}

# Notify subscribers of an event
event_notify() {
    local type="$1"
    local event_file="$2"
    local subscribers_dir
    subscribers_dir="$(get_subscribers_dir)"
    
    if [ ! -d "$subscribers_dir" ]; then
        return 0
    fi
    
    # Call each subscriber that handles this event type
    for subscriber in "$subscribers_dir"/*.sh; do
        [ -f "$subscriber" ] || continue
        
        # Check if subscriber handles this event type
        if grep -q "# Event: $type" "$subscriber" 2>/dev/null || grep -q "# Event: *" "$subscriber" 2>/dev/null; then
            source "$subscriber" 2>/dev/null && {
                if type handle_event >/dev/null 2>&1; then
                    handle_event "$type" "$event_file" 2>/dev/null || true
                fi
            }
        fi
    done
}

# List recent events
event_list() {
    local limit="${1:-20}"
    local events_dir
    events_dir="$(get_events_dir)"
    
    echo "RECENT EVENTS"
    echo "═══════════════════════════════════════"
    
    if [ ! -d "$events_dir" ]; then
        echo "  (no events recorded)"
        return
    fi
    
    local count=0
    for event_file in $(ls -1t "$events_dir"/*.yaml 2>/dev/null); do
        [ $count -ge "$limit" ] && break
        local type source timestamp
        type="$(yaml_get_nested "$event_file" "event" "type" 2>/dev/null)"
        source="$(yaml_get_nested "$event_file" "event" "source" 2>/dev/null)"
        timestamp="$(yaml_get_nested "$event_file" "event" "timestamp" 2>/dev/null)"
        echo "  [$timestamp] $type ($source)"
        count=$((count + 1))
    done
    
    [ $count -eq 0 ] && echo "  (no events recorded)"
}

# Query events by type
event_query() {
    local type="$1"
    local events_dir
    events_dir="$(get_events_dir)"
    
    echo "Events of type: $type"
    echo "═══════════════════════════════════════"
    
    if [ ! -d "$events_dir" ]; then
        echo "  (no events)"
        return
    fi
    
    local found=0
    for event_file in "$events_dir"/*.yaml; do
        [ -f "$event_file" ] || continue
        local etype
        etype="$(yaml_get_nested "$event_file" "event" "type" 2>/dev/null)"
        if [ "$etype" = "$type" ]; then
            local id source timestamp
            id="$(yaml_get_nested "$event_file" "event" "id" 2>/dev/null)"
            source="$(yaml_get_nested "$event_file" "event" "source" 2>/dev/null)"
            timestamp="$(yaml_get_nested "$event_file" "event" "timestamp" 2>/dev/null)"
            echo "  $id: $source @ $timestamp"
            found=$((found + 1))
        fi
    done
    
    [ $found -eq 0 ] && echo "  (no matching events)"
}

# Count events
event_count() {
    local events_dir
    events_dir="$(get_events_dir)"
    if [ -d "$events_dir" ]; then
        ls -1 "$events_dir"/*.yaml 2>/dev/null | wc -l | tr -d ' '
    else
        echo "0"
    fi
}

# Clear old events
event_prune() {
    local keep="${1:-100}"
    local events_dir
    events_dir="$(get_events_dir)"
    
    if [ ! -d "$events_dir" ]; then
        return 0
    fi
    
    local total
    total="$(event_count)"
    if [ "$total" -gt "$keep" ]; then
        local remove=$((total - keep))
        ls -1t "$events_dir"/*.yaml 2>/dev/null | tail -"$remove" | xargs rm -f 2>/dev/null
        log_info "Pruned $remove old events"
    fi
}

# Initialize event system
event_init() {
    local events_dir subscribers_dir
    events_dir="$(get_events_dir)"
    subscribers_dir="$(get_subscribers_dir)"
    
    ensure_dir "$events_dir"
    ensure_dir "$subscribers_dir"
    
    log_info "Event system initialized"
    echo "Event system ready"
}
