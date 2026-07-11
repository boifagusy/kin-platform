#!/data/data/com.termux/files/usr/bin/bash
# Universal Project Detector
# Works across KIN, VinePay, FlashFlow, HyperMind

detect_project_root() {
    # Walk up from current directory to find project root
    local dir="${1:-$(pwd)}"
    
    while [ "$dir" != "/" ]; do
        # Check for Engineering OS marker
        if [ -d "$dir/.sdk" ] && [ -d "$dir/.kin" ]; then
            echo "$dir"
            return 0
        fi
        # Check for common project markers
        if [ -f "$dir/artisan" ] || [ -f "$dir/package.json" ] || [ -f "$dir/composer.json" ]; then
            if [ -d "$dir/.git" ]; then
                echo "$dir"
                return 0
            fi
        fi
        dir=$(dirname "$dir")
    done
    
    # Fallback
    echo "$HOME/kin_project"
    return 1
}

detect_backend_root() {
    local project_root
    project_root=$(detect_project_root)
    
    # Check common backend locations
    for dir in "$project_root/backend" "$project_root" "$project_root/api"; do
        if [ -f "$dir/artisan" ]; then
            echo "$dir"
            return 0
        fi
    done
    
    echo "$project_root"
}

detect_frontend_root() {
    local project_root
    project_root=$(detect_project_root)
    
    for dir in "$project_root/frontend" "$project_root" "$project_root/web"; do
        if [ -f "$dir/package.json" ]; then
            echo "$dir"
            return 0
        fi
    done
    
    echo "$project_root"
}

# Export for other engines
export PROJECT_ROOT
PROJECT_ROOT=$(detect_project_root)
export BACKEND_ROOT
BACKEND_ROOT=$(detect_backend_root)
export FRONTEND_ROOT
FRONTEND_ROOT=$(detect_frontend_root)

# Source this in any engine:
# source "$(dirname "$0")/../../project.sh"
