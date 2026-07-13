# Description: Watch mode — monitor project state continuously
watch_main() {
    echo "ENGINEERING OS — WATCH MODE"
    echo "Watching for changes... (Ctrl+C to stop)"
    echo ""
    
    local last_hash=""
    while true; do
        # Check git changes
        local current_hash=$(git rev-parse HEAD 2>/dev/null)
        local changes=$(git status --porcelain 2>/dev/null | wc -l | tr -d ' ')
        
        if [ "$current_hash" != "$last_hash" ] && [ -n "$last_hash" ]; then
            echo "[$(date +%H:%M:%S)] New commit detected"
            source .sdk/kernel/autodetect.sh 2>/dev/null
            autodetect_brick 2>/dev/null
        fi
        
        if [ "$changes" -gt 10 ]; then
            echo "[$(date +%H:%M:%S)] ⚠️  $changes uncommitted changes"
        fi
        
        last_hash="$current_hash"
        sleep 10
    done
}
main() { watch_main "$@"; }
