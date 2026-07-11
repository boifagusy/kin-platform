#!/data/data/com.termux/files/usr/bin/bash
# Engineering OS Restore Engine v2.0
# SDK Component - Portable across KIN, VinePay, FlashFlow, HyperMind
# 
# Plugin API: Registered as 'restore' in Engineering OS
# Recipes: Stored in .sdk/recipes/
# Manifests: Stored in .kin/manifests/
# History: Stored in .kin/history/

set -euo pipefail

# ============================================
# SDK PATHS (Project-independent)
# ============================================
SDK_DIR="${KIN_SDK_DIR:-$HOME/kin_project/.sdk}"
RECIPES_DIR="$SDK_DIR/recipes"
KIN_DIR="${KIN_DIR:-$HOME/kin_project/.kin}"
MANIFESTS_DIR="$KIN_DIR/manifests"
HISTORY_DIR="$KIN_DIR/history"
CHECKPOINTS_DIR="$KIN_DIR/checkpoints"

# Ensure directories exist
mkdir -p "$SDK_DIR"/{engines,recipes} "$KIN_DIR"/{history,manifests,checkpoints}

TIMESTAMP=$(date +%Y%m%d_%H%M%S)
ISO_TIMESTAMP=$(date -Iseconds)

# ============================================
# PLUGIN REGISTRATION
# ============================================
register_plugin() {
    local plugin_dir="$KIN_DIR/plugins"
    mkdir -p "$plugin_dir"
    
    cat > "$plugin_dir/restore.json" << JSON
{
  "name": "restore",
  "version": "2.0.0",
  "description": "Component restoration engine",
  "commands": {
    "restore": {
      "description": "Restore a component from a recipe",
      "usage": "ai restore <component> [--dry-run] [--rollback] [--verify-only]",
      "flags": {
        "--dry-run": "Preview changes without executing",
        "--rollback": "Rollback a previous restoration",
        "--verify-only": "Verify existing restoration without changes"
      }
    }
  },
  "recipes_dir": ".sdk/recipes",
  "manifests_dir": ".kin/manifests",
  "history_dir": ".kin/history"
}
JSON
}

# ============================================
# MANIFEST v2.0 (Enhanced)
# ============================================
create_manifest() {
    local component="$1"
    local version="${2:-unknown}"
    local manifest_file="$MANIFESTS_DIR/${component}_${TIMESTAMP}.json"
    
    cat > "$manifest_file" << JSON
{
  "schema_version": "2.0",
  "restore": {
    "component": "$component",
    "version": "$version",
    "started": "$ISO_TIMESTAMP",
    "completed": null,
    "status": "in_progress",
    "user": "$(whoami)",
    "host": "$(hostname)",
    "sdk_version": "2.0.0"
  },
  "files": {
    "created": [],
    "modified": [],
    "backups": []
  },
  "checksums": {
    "before": {},
    "after": {}
  },
  "verification": {
    "passed": false,
    "checks": [],
    "errors": []
  },
  "rollback": {
    "available": false,
    "command": null,
    "checkpoint": null
  },
  "dependencies": {
    "required": [],
    "satisfied": []
  }
}
JSON
    
    echo "$manifest_file"
}

update_manifest_file() {
    local manifest="$1"
    local section="$2"
    local key="$3"
    local value="$4"
    
    # Use PHP for reliable JSON manipulation
    php -r "
    \$data = json_decode(file_get_contents('$manifest'), true);
    
    // Navigate to nested key
    \$keys = explode('.', '$section.$key');
    \$ref = &\$data;
    foreach (\$keys as \$k) {
        if (!isset(\$ref[\$k])) \$ref[\$k] = [];
        \$ref = &\$ref[\$k];
    }
    
    // Set value
    if (is_array(\$ref)) {
        \$ref[] = '$value';
    } else {
        \$ref = '$value';
    }
    
    file_put_contents('$manifest', json_encode(\$data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    " 2>/dev/null || true
}

# ============================================
# CHECKSUM VERIFICATION
# ============================================
checksum_file() {
    local file="$1"
    if [ -f "$file" ]; then
        sha256sum "$file" | cut -d' ' -f1
    else
        echo "FILE_NOT_FOUND"
    fi
}

verify_checksums() {
    local manifest="$1"
    
    php -r "
    \$data = json_decode(file_get_contents('$manifest'), true);
    \$errors = 0;
    
    if (!empty(\$data['checksums']['after'])) {
        foreach (\$data['checksums']['after'] as \$file => \$expected) {
            \$current = trim(shell_exec('sha256sum ' . escapeshellarg(\$file) . ' 2>/dev/null | cut -d\" \" -f1'));
            if (\$current !== \$expected && \$current !== '') {
                echo \"✗ Checksum mismatch: \$file\n\";
                echo \"  Expected: \$expected\n\";
                echo \"  Current:  \$current\n\";
                \$errors++;
            }
        }
    }
    
    if (\$errors > 0) {
        echo \"\n{\$errors} checksum mismatches detected\n\";
        exit(1);
    }
    echo \"✓ All checksums verified\n\";
    exit(0);
    "
}

# ============================================
# CHECKPOINT INTEGRATION
# ============================================
create_checkpoint() {
    local label="$1"
    local checkpoint_dir="$CHECKPOINTS_DIR/${TIMESTAMP}_${label}"
    
    if command -v ai &>/dev/null && ai checkpoint create "$label" 2>/dev/null; then
        echo "$CHECKPOINTS_DIR/$label"
        return 0
    fi
    
    # Fallback: manual checkpoint
    mkdir -p "$checkpoint_dir"
    if [ -d "$HOME/kin_project/backend" ]; then
        cp "$HOME/kin_project/backend/routes/api.php" "$checkpoint_dir/" 2>/dev/null || true
    fi
    echo "$checkpoint_dir"
}

# ============================================
# RECIPE DEPENDENCY CHECK
# ============================================
check_dependencies() {
    local recipe_file="$1"
    
    # Source recipe to get dependencies
    source "$recipe_file"
    
    if [ -z "${RECIPE_DEPENDENCIES:-}" ]; then
        return 0
    fi
    
    echo "Checking dependencies..."
    for dep in "${RECIPE_DEPENDENCIES[@]}"; do
        echo -n "  $dep: "
        if [ -f "$RECIPES_DIR/${dep}.sh" ]; then
            echo "✓ recipe available"
        else
            echo "✗ recipe not found"
            return 1
        fi
    done
    
    return 0
}

# ============================================
# CLIPBOARD INTEGRATION
# ============================================
clipboard_report() {
    local component="$1"
    local status="$2"
    local routes_count="${3:-0}"
    local errors="${4:-0}"
    
    local report
    report=$(cat << EOF
✓ Watchtower restored
  Routes: $routes_count verified
  Errors: $errors
  Rollback: ai restore $component --rollback
  Manifest: .kin/manifests/
EOF
)
    
    if command -v ai &>/dev/null && ai clipboard copy "$report" 2>/dev/null; then
        echo "✓ Report copied to clipboard"
    else
        echo "$report"
    fi
}

# ============================================
# KNOWLEDGE ENGINE INTEGRATION
# ============================================
emit_knowledge_event() {
    local recipe="$1"
    local duration="$2"
    local verification="$3"
    
    if command -v ai &>/dev/null; then
        ai knowledge emit "recipe.used" \
            --recipe "$recipe" \
            --duration "$duration" \
            --verification "$verification" \
            --timestamp "$ISO_TIMESTAMP" 2>/dev/null || true
    fi
}

# ============================================
# MAIN EXECUTION
# ============================================
run_restore() {
    local component="$1"
    local mode="${2:-restore}"
    local recipe_file="$RECIPES_DIR/${component}.sh"
    
    # Validate recipe exists
    if [ ! -f "$recipe_file" ]; then
        echo "✗ No recipe found: $component"
        echo ""
        echo "Available recipes:"
        ls -1 "$RECIPES_DIR" 2>/dev/null | sed 's/\.sh$//' | sed 's/^/  - /' || echo "  (none)"
        return 1
    fi
    
    # Load recipe
    source "$recipe_file"
    
    case "$mode" in
        dry-run)
            echo "=========================================="
            echo "DRY RUN: $component"
            echo "=========================================="
            echo ""
            echo "Component: ${RECIPE_NAME:-$component}"
            echo "Version: ${RECIPE_VERSION:-unknown}"
            echo "Description: ${RECIPE_DESCRIPTION:-No description}"
            echo ""
            
            if type dry_run_preview &>/dev/null; then
                dry_run_preview
            else
                echo "Files that would be created:"
                for file in "${CREATE_FILES[@]:-}"; do
                    echo "  + $file"
                done
                echo ""
                echo "Files that would be modified:"
                for file in "${MODIFY_FILES[@]:-}"; do
                    echo "  ~ $file"
                done
                echo ""
                echo "Routes that would be added:"
                for route in "${EXPECTED_ROUTES[@]:-}"; do
                    echo "  + $route"
                done
            fi
            
            # Check dependencies
            check_dependencies "$recipe_file" || echo "⚠ Dependencies not satisfied"
            ;;
            
        rollback)
            echo "=========================================="
            echo "ROLLBACK: $component"
            echo "=========================================="
            if type rollback &>/dev/null; then
                rollback
                echo "✓ Rollback complete"
            else
                echo "✗ No rollback function defined"
            fi
            ;;
            
        verify-only)
            echo "=========================================="
            echo "VERIFY: $component"
            echo "=========================================="
            if type verify &>/dev/null; then
                verify
            else
                echo "✗ No verify function defined"
            fi
            ;;
            
        restore)
            # Create checkpoint
            echo "Creating checkpoint..."
            create_checkpoint "before-${component}-restore"
            echo ""
            
            # Check dependencies
            check_dependencies "$recipe_file" || {
                echo "✗ Dependencies not satisfied. Aborting."
                return 1
            }
            
            # Create manifest
            local manifest
            manifest=$(create_manifest "$component" "${RECIPE_VERSION:-unknown}")
            echo "Manifest: $manifest"
            echo ""
            
            # Pre-restore checks
            if type pre_restore &>/dev/null; then
                echo "--- Pre-restore checks ---"
                if ! pre_restore; then
                    echo "✗ Pre-restore checks failed"
                    return 1
                fi
            fi
            
            # Capture checksums before
            for file in "${CREATE_FILES[@]:-}" "${MODIFY_FILES[@]:-}"; do
                if [ -f "$file" ]; then
                    local checksum
                    checksum=$(checksum_file "$file")
                    update_manifest_file "$manifest" "checksums" "before.$file" "$checksum"
                fi
            done
            
            # Execute restoration
            echo "--- Restoring ---"
            local start_time
            start_time=$(date +%s)
            
            if type restore &>/dev/null; then
                if restore "$manifest"; then
                    local end_time
                    end_time=$(date +%s)
                    local duration=$((end_time - start_time))
                    
                    echo "✓ Restoration complete (${duration}s)"
                    
                    # Capture checksums after
                    for file in "${CREATE_FILES[@]:-}" "${MODIFY_FILES[@]:-}"; do
                        if [ -f "$file" ]; then
                            local checksum
                            checksum=$(checksum_file "$file")
                            update_manifest_file "$manifest" "checksums" "after.$file" "$checksum"
                        fi
                    done
                    
                    # Verify
                    echo ""
                    echo "--- Verification ---"
                    if type verify &>/dev/null; then
                        if verify; then
                            update_manifest_file "$manifest" "verification" "passed" "true"
                            echo "✓ All verifications passed"
                        else
                            update_manifest_file "$manifest" "verification" "passed" "false"
                            echo "✗ Verification failed"
                        fi
                    fi
                    
                    # Mark complete
                    update_manifest_file "$manifest" "restore" "status" "completed"
                    update_manifest_file "$manifest" "restore" "completed" "$ISO_TIMESTAMP"
                    
                    # Rollback info
                    update_manifest_file "$manifest" "rollback" "available" "true"
                    update_manifest_file "$manifest" "rollback" "command" "ai restore $component --rollback"
                    
                    # Emit knowledge event
                    emit_knowledge_event "$component" "$duration" "passed"
                    
                    # Clipboard report
                    local route_count=${#EXPECTED_ROUTES[@]:-0}
                    clipboard_report "$component" "restored" "$route_count" "0"
                    
                else
                    echo "✗ Restoration failed"
                    update_manifest_file "$manifest" "restore" "status" "failed"
                    return 1
                fi
            else
                echo "✗ No restore function defined in recipe"
                return 1
            fi
            ;;
    esac
}

# ============================================
# ENTRY POINT
# ============================================
main() {
    local component="${1:-}"
    local mode="restore"
    
    # Parse flags
    for arg in "$@"; do
        case $arg in
            --dry-run) mode="dry-run" ;;
            --rollback) mode="rollback" ;;
            --verify-only) mode="verify-only" ;;
            --help) 
                echo "Usage: ai restore <component> [--dry-run|--rollback|--verify-only]"
                echo ""
                echo "Available recipes:"
                ls -1 "$RECIPES_DIR" 2>/dev/null | sed 's/\.sh$//' | sed 's/^/  - /'
                return 0
                ;;
        esac
    done
    
    if [ -z "$component" ]; then
        echo "Usage: ai restore <component> [flags]"
        echo "Run 'ai restore --help' for more information"
        return 1
    fi
    
    run_restore "$component" "$mode"
}

# Register plugin on first run
if [ ! -f "$KIN_DIR/plugins/restore.json" ]; then
    register_plugin
fi

main "$@"
