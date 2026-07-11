#!/data/data/com.termux/files/usr/bin/bash

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/errors.sh" 2>/dev/null || true

# Get project root
get_project_root() {
    git rev-parse --show-toplevel 2>/dev/null
}

# Detect project name
get_project_name() {
    local root
    root="$(get_project_root)" || return 1
    basename "$root"
}

# Detect backend framework
detect_backend() {
    local root
    root="$(get_project_root)" || return 1
    
    if [ -f "$root/composer.json" ]; then
        if grep -q "laravel" "$root/composer.json" 2>/dev/null; then
            echo "laravel"
            return 0
        fi
        echo "php"
        return 0
    fi
    
    if [ -f "$root/go.mod" ]; then
        echo "go"
        return 0
    fi
    
    if [ -f "$root/package.json" ]; then
        if grep -q '"express"' "$root/package.json" 2>/dev/null; then
            echo "express"
            return 0
        fi
        echo "node"
        return 0
    fi
    
    echo "unknown"
    return 1
}

# Detect frontend/mobile framework
detect_frontend() {
    local root
    root="$(get_project_root)" || return 1
    
    if [ -f "$root/package.json" ]; then
        if grep -q '"react-native"' "$root/package.json" 2>/dev/null; then
            echo "react-native"
            return 0
        fi
        if grep -q '"react"' "$root/package.json" 2>/dev/null; then
            echo "react"
            return 0
        fi
        if grep -q '"vue"' "$root/package.json" 2>/dev/null; then
            echo "vue"
            return 0
        fi
        if grep -q '"next"' "$root/package.json" 2>/dev/null; then
            echo "next"
            return 0
        fi
        echo "node"
        return 0
    fi
    
    if [ -f "$root/pubspec.yaml" ]; then
        if grep -q "flutter" "$root/pubspec.yaml" 2>/dev/null; then
            echo "flutter"
            return 0
        fi
    fi
    
    echo "unknown"
    return 1
}

# Detect database type
detect_database() {
    local root
    root="$(get_project_root)" || return 1
    
    if [ -f "$root/.env" ]; then
        if grep -q "DB_CONNECTION=mysql" "$root/.env" 2>/dev/null; then
            echo "mysql"
            return 0
        fi
        if grep -q "DB_CONNECTION=pgsql" "$root/.env" 2>/dev/null; then
            echo "postgresql"
            return 0
        fi
        if grep -q "DB_CONNECTION=sqlite" "$root/.env" 2>/dev/null; then
            echo "sqlite"
            return 0
        fi
    fi
    
    if [ -f "$root/composer.json" ]; then
        if grep -q "mysql" "$root/composer.json" 2>/dev/null; then
            echo "mysql"
            return 0
        fi
    fi
    
    echo "unknown"
    return 1
}

# Detect all project capabilities
detect_project() {
    local root
    root="$(get_project_root)" || {
        throw $E_GIT_NOT_FOUND
        return 1
    }
    
    cat <<YAML
project:
  root: $root
  name: $(get_project_name)
  backend: $(detect_backend)
  frontend: $(detect_frontend)
  database: $(detect_database)
  termux: $(is_termux && echo "true" || echo "false")
  git_branch: $(git -C "$root" branch --show-current 2>/dev/null || echo "unknown")
YAML
}

# Check if project has a specific feature
project_has() {
    local feature="$1"
    local root
    root="$(get_project_root)" || return 1
    
    case "$feature" in
        laravel) [ -f "$root/artisan" ] && return 0 ;;
        react)   grep -q '"react"' "$root/package.json" 2>/dev/null && return 0 ;;
        flutter) [ -f "$root/pubspec.yaml" ] && return 0 ;;
        docker)  [ -f "$root/Dockerfile" ] || [ -f "$root/docker-compose.yml" ] && return 0 ;;
        github)  [ -d "$root/.github" ] && return 0 ;;
        *)       return 1 ;;
    esac
}
