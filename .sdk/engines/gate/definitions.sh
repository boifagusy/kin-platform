#!/data/data/com.termux/files/usr/bin/bash

# Gate definitions - source of truth for all 11 gates

GATE_DEFINITIONS=(
    "0:Bootstrap:Initialize project, install SDK, verify environment"
    "1:Discovery:Understand existing codebase or requirements"
    "2:Requirements:Define what must be built"
    "3:Architecture:Design system architecture, create ADRs"
    "4:Dependency Planning:Map all brick dependencies"
    "5:Brick Planning:Plan each brick's implementation"
    "6:Brick Development:Implement each brick"
    "7:Brick Testing:Test each brick independently"
    "8:Integration Testing:Test bricks working together"
    "9:System Testing:Full system validation"
    "10:Production Validation:Production readiness"
    "11:Release:Release to production"
)

# Gate entry requirements
gate_entry_requirements() {
    local gate="$1"
    case "$gate" in
        0)  echo "git_repo: true" ;;
        1)  echo "gate_0_complete: true" ;;
        2)  echo "gate_1_complete: true" ;;
        3)  echo "gate_2_complete: true
approval: engineering_manager" ;;
        4)  echo "gate_3_complete: true
architecture_docs: approved" ;;
        5)  echo "gate_4_complete: true
dependency_graph: validated" ;;
        6)  echo "gate_5_complete: true
brick_plans: approved" ;;
        7)  echo "gate_6_complete: true
brick_implementation: complete" ;;
        8)  echo "gate_7_complete: true
brick_tests: passing" ;;
        9)  echo "gate_8_complete: true
integration_tests: passing" ;;
        10) echo "gate_9_complete: true
system_tests: passing" ;;
        11) echo "gate_10_complete: true
production_checklist: complete
approval: engineering_manager" ;;
        *) echo "unknown_gate: true" ;;
    esac
}

# Gate exit requirements
gate_exit_requirements() {
    local gate="$1"
    case "$gate" in
        0) echo "sdk_installed: true
doctor_passes: true
session_active: true
documents: [PROJECT_DNA.yaml, README.md]" ;;
        1) echo "discovery_complete: true
cache_populated: true
documents: [DISCOVERY.md]" ;;
        2) echo "requirements_approved: true
documents: [REQUIREMENTS.md]" ;;
        3) echo "architecture_approved: true
adrs_created: true
documents: [ARCHITECTURE.md, docs/adr/]" ;;
        4) echo "dependency_graph_validated: true
no_circular_deps: true
documents: [DEPENDENCY_GRAPH.md]" ;;
        5) echo "brick_plans_approved: true
brick_count: minimum_1
documents: [BRICK_PLAN.md]" ;;
        6) echo "bricks_implemented: true
unit_tests: passing
documents: [brick.yaml]" ;;
        7) echo "brick_tests_passing: true
api_tests: passing
contract_tests: passing
documents: [TEST_REPORT.md]" ;;
        8) echo "integration_passing: true
ui_tests: passing
documents: [INTEGRATION_REPORT.md]" ;;
        9) echo "system_tests_passing: true
runtime_tests: passing
performance_tests: passing
security_tests: passing
documents: [SYSTEM_TEST_REPORT.md]" ;;
        10) echo "production_checklist: complete
benchmarks: passing
documents: [PRODUCTION_CHECKLIST.md]" ;;
        11) echo "release_deployed: true
post_release_verified: true
documents: [RELEASE_NOTES.md, CHANGELOG.md]" ;;
        *) echo "unknown_gate: true" ;;
    esac
}

# Get gate name
gate_name() {
    local gate="$1"
    for def in "${GATE_DEFINITIONS[@]}"; do
        if [ "${def%%:*}" = "$gate" ]; then
            echo "${def}" | cut -d: -f2
            return
        fi
    done
    echo "Unknown"
}

# Get gate description
gate_description() {
    local gate="$1"
    for def in "${GATE_DEFINITIONS[@]}"; do
        if [ "${def%%:*}" = "$gate" ]; then
            echo "${def}" | cut -d: -f3
            return
        fi
    done
    echo "No description"
}

# Total number of gates
gate_count() {
    echo "${#GATE_DEFINITIONS[@]}"
}
