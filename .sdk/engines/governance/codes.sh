#!/data/data/com.termux/files/usr/bin/bash

# Governance Decision Codes

decision_code() {
    local guard="$1"
    case "$guard" in
        context)    echo "GOV-CTX-001" ;;
        session)    echo "GOV-SES-001" ;;
        role)       echo "GOV-ROL-001" ;;
        gate)       echo "GOV-GAT-004" ;;
        brick)      echo "GOV-BRK-002" ;;
        approval)   echo "GOV-APR-001" ;;
        dependency) echo "GOV-DEP-003" ;;
        validation) echo "GOV-VAL-003" ;;
        blocked)    echo "GOV-BLK-001" ;;
        git_clean)  echo "GOV-GIT-005" ;;
        release)    echo "GOV-REL-001" ;;
        *)          echo "GOV-UNK-000" ;;
    esac
}

decision_reason() {
    local guard="$1"
    case "$guard" in
        context)    echo "Not in a project directory" ;;
        session)    echo "No active session" ;;
        role)       echo "Role not assigned" ;;
        gate)       echo "Gate too early for this command" ;;
        brick)      echo "No active brick selected" ;;
        approval)   echo "Engineering Manager approval required" ;;
        dependency) echo "Unmet brick dependencies" ;;
        validation) echo "Previous brick not certified" ;;
        blocked)    echo "Gate is blocked" ;;
        git_clean)  echo "Git has uncommitted changes" ;;
        release)    echo "Release readiness check failed" ;;
        *)          echo "Unknown governance failure" ;;
    esac
}

decision_fix() {
    local guard="$1"
    case "$guard" in
        context)    echo "cd ~/kin_project" ;;
        session)    echo "ai session start" ;;
        role)       echo "ai role set <role>" ;;
        gate)       echo "ai gate verify && ai gate advance" ;;
        brick)      echo "ai brick create <name>" ;;
        approval)   echo "Request Engineering Manager approval" ;;
        dependency) echo "Create dependent bricks first" ;;
        validation) echo "Complete and certify previous brick" ;;
        blocked)    echo "Resolve the blocker" ;;
        git_clean)  echo "Commit or stash changes" ;;
        release)    echo "Complete release checklist" ;;
        *)          echo "Review governance requirements" ;;
    esac
}
