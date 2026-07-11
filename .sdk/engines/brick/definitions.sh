#!/data/data/com.termux/files/usr/bin/bash

# Standard brick directory structure
BRICK_DIRS=(
    "contracts"
    "events"
    "policies"
    "database/migrations"
    "database/seeds"
    "backend/controllers"
    "backend/services"
    "backend/models"
    "backend/routes"
    "frontend/components"
    "frontend/screens"
    "frontend/hooks"
    "api"
    "tests/unit"
    "tests/api"
    "tests/integration"
    "tests/ui"
    "tests/benchmarks"
    "benchmarks"
    "performance"
    "security"
    "fixtures"
    "examples"
    "docs"
)

# Brick state values
readonly BRICK_PLANNED="planned"
readonly BRICK_DEVELOPMENT="in_development"
readonly BRICK_TESTING="testing"
readonly BRICK_COMPLETE="complete"
readonly BRICK_RELEASED="released"

# Brick template for brick.yaml
brick_template() {
    local name="$1"
    local version="${2:-1.0.0}"
    cat <<YAML
brick:
  name: $name
  version: $version
  status: planned
  description: ""
  dependencies: []
  capabilities: []
  contracts: []
  events: []
  gate: 6
  assigned_ai: null
  locked: false
  locked_by: null
  created: $(date -u +%Y-%m-%dT%H:%M:%SZ)
  last_updated: $(date -u +%Y-%m-%dT%H:%M:%SZ)
  tests:
    unit: 0
    api: 0
    integration: 0
    ui: 0
    benchmarks: 0
YAML
}
