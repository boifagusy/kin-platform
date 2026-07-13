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
