#!/data/data/com.termux/files/usr/bin/bash
source "$(dirname "$0")/framework.sh"

BACKEND_DIR="$1"
REPORT_DIR="$2"
TEMP_DIR="$3"
cd "$BACKEND_DIR"

REPORT="$REPORT_DIR/10_feature_scoring.md"

{
    echo "# Feature Health Scoring"
    echo ""
    echo "## Scoring Matrix"
    echo ""
    echo "Feature | Score | Controller | Route | Service | Frontend | Status"
    echo "---------|-------|------------|-------|---------|----------|--------"
    
    # Score each feature
    SCORE_FEATURE "Authentication" "*Auth*" "login\|register\|logout" "auth\|login\|register"
    SCORE_FEATURE "Dashboard" "*Dashboard*" "dashboard" "dashboard"
    SCORE_FEATURE "Activities" "*Activity*" "activity\|activities" "activity"
    SCORE_FEATURE "SOS" "*SOS*" "sos" "sos"
    SCORE_FEATURE "Check-in" "*CheckIn*" "checkin\|check-in" "checkin"
    SCORE_FEATURE "Trusted Contacts" "*TrustedContact*" "trusted-contact\|trusted_contact" "trusted.contact"
    SCORE_FEATURE "Incidents" "*Incident*" "incident" "incident"
    SCORE_FEATURE "Alerts" "*Alert*" "alert" "alert"
    SCORE_FEATURE "Network" "*Network*" "network" "network"
    SCORE_FEATURE "Safety" "*Safety*" "safety" "safety"
    SCORE_FEATURE "Watchtower" "*Watchtower*" "watchtower" "watchtower"
    SCORE_FEATURE "API Monitor" "*ApiMonitor*" "api-monitor\|api_monitor" "api.monitor"
    SCORE_FEATURE "Queue Monitor" "*QueueMonitor*" "queue-monitor\|queue_monitor" "queue.monitor"
    SCORE_FEATURE "Database Monitor" "*DatabaseMonitor*" "database-monitor" "database.monitor"
    SCORE_FEATURE "Performance Monitor" "*Performance*" "performance" "performance"
    SCORE_FEATURE "Health" "*Health*" "health" "health"
    
} > "$REPORT" 2>&1

echo "Phase 10 complete"
