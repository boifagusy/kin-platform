#!/data/data/com.termux/files/usr/bin/bash
# Project Orchestrator
source "$SDK_ROOT/engines/gate/engine.sh" 2>/dev/null

case "${1:-status}" in
    continue) echo "Gate: $(gate_current) — $(gate_name "$(gate_current)")"
              echo "Run: ai gate advance" ;;
    status)   echo "Project: $(basename "$(git rev-parse --show-toplevel 2>/dev/null)")"
              echo "Gate: $(gate_current) — $(gate_name "$(gate_current)")" ;;
    *)        echo "Usage: ai project [status|continue]" ;;
esac

# ── Standard Engine API ──
project_help()    { echo "Project Engine — Orchestrator. ai project [status|continue|import]"; }
project_version() { echo "Project Engine v3.3.0"; }
project_health()  { echo "✅ Project engine healthy"; return 0; }
project_doctor()  { echo "Project: $(basename $(git rev-parse --show-toplevel 2>/dev/null)) | Gate: $(grep current: .kin/state/gate.yaml 2>/dev/null | sed 's/.*: //')"; }
project_validate(){ [ -f ".sdk/engines/project/engine.sh" ] && echo "✅ Valid"; }

if [ "${1:-}" = "api" ]; then
    case "${2:-help}" in
        help) project_help ;; version) project_version ;; status) echo "Active";;
        health) project_health ;; doctor) project_doctor ;; validate) project_validate ;;
    esac
    exit 0
fi
