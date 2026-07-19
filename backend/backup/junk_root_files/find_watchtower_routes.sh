#!/data/data/com.termux/files/usr/bin/bash
cd ~/kin_project/backend

echo "=== All route backup files ==="
ls -la routes/*.backup* routes/*.bak* routes/*.before* routes/*.clean* routes/*.final* routes/*.pre_prod* routes/*.broken* routes/*.incidents* 2>/dev/null

echo ""
echo "=== Searching each backup for Watchtower ==="
for backup in routes/*.backup* routes/*.bak* routes/*.before* routes/*.clean* routes/*.final* routes/*.pre_prod* routes/*.broken* routes/*.incidents*; do
    if [ -f "$backup" ]; then
        count=$(grep -ci "watchtower\|Monitor\|QueueMonitor\|ApiMonitor\|DatabaseMonitor\|PerformanceMonitor\|PluginHealth\|ErrorMonitor\|NotificationMonitor\|SecurityMonitor\|SafetyEngineMonitor\|WatchtowerHealth" "$backup" 2>/dev/null)
        if [ "$count" -gt 0 ]; then
            echo ""
            echo "=== $backup ($count Watchtower references, $(wc -l < "$backup") lines) ==="
            grep -n "watchtower\|Monitor\|QueueMonitor\|ApiMonitor\|DatabaseMonitor\|PerformanceMonitor\|PluginHealth\|ErrorMonitor\|NotificationMonitor\|SecurityMonitor\|SafetyEngineMonitor\|WatchtowerHealth" "$backup"
        fi
    fi
done

echo ""
echo "=== Current api.php Watchtower references ==="
grep -c "watchtower\|Monitor" routes/api.php 2>/dev/null || echo "0"

echo ""
echo "=== ApiMonitorController - what methods SHOULD it have? ==="
echo "ApiMonitorService methods:"
grep "public function " app/Http/Controllers/Watchtower/ApiMonitorService.php 2>/dev/null
