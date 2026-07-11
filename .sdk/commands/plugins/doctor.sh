# Description: Run environment diagnostics
doctor_main() {
    local project_root
    project_root="$(get_project_root)" || return 1
    local errors=0
    local warnings=0

    echo "KIN ENGINEERING SDK - DOCTOR"
    echo "═══════════════════════════════════════"
    echo ""

    echo "ENVIRONMENT"
    echo "───────────────────────────────────────"

    if is_termux; then
        echo "  Termux:      detected"
    else
        echo "  Termux:      NOT DETECTED"
        errors=$((errors + 1))
    fi

    if git rev-parse --git-dir > /dev/null 2>&1; then
        echo "  Git:         repository found"
    else
        echo "  Git:         NOT A REPOSITORY"
        errors=$((errors + 1))
    fi

    echo ""

    echo "SDK"
    echo "───────────────────────────────────────"

    if [ -f "$project_root/.sdk/sdk.yaml" ]; then
        local version
        version="$(read_yaml "$project_root/.sdk/sdk.yaml" "version")"
        echo "  Installed:   v${version}"
    else
        echo "  Installed:   NOT FOUND"
        errors=$((errors + 1))
    fi

    if [ -f "$project_root/.sdk/commands/ai" ] && [ -x "$project_root/.sdk/commands/ai" ]; then
        echo "  CLI:         operational"
    else
        echo "  CLI:         MISSING"
        errors=$((errors + 1))
    fi

    local plugin_count
    plugin_count=$(find "$project_root/.sdk/commands/plugins" -name "*.sh" 2>/dev/null | wc -l | tr -d ' ')
    echo "  Plugins:     ${plugin_count} loaded"

    echo ""

    echo "STATE"
    echo "───────────────────────────────────────"

    if [ -f "$project_root/.kin/state/session.yaml" ]; then
        local status
        status="$(read_yaml "$project_root/.kin/state/session.yaml" "status")"
        echo "  Session:     ${status:-unknown}"
    else
        echo "  Session:     not initialized"
        warnings=$((warnings + 1))
    fi

    if [ -f "$project_root/.kin/state/ai.yaml" ]; then
        local role stage
        role="$(read_yaml "$project_root/.kin/state/ai.yaml" "active_role")"
        stage="$(read_yaml "$project_root/.kin/state/ai.yaml" "current_stage")"
        echo "  Role:        ${role:-unassigned}"
        echo "  Stage:       ${stage:-unknown}"
    else
        echo "  AI State:    not found"
        warnings=$((warnings + 1))
    fi

    echo ""

    echo "GOVERNANCE"
    echo "───────────────────────────────────────"

    local gov_dir="$project_root/docs/governance"
    if [ -d "$gov_dir" ]; then
        local gov_count
        gov_count=$(find "$gov_dir" -type f 2>/dev/null | wc -l | tr -d ' ')
        echo "  Documents:   ${gov_count} found"

        local required=("AGENT_PROTOCOL.md" "AI_CONTRACT.yaml" "ENGINEERING_MANAGER.md" "KIN_ENGINEERING_OS.md" "PROJECT_DNA.yaml")
        for doc in "${required[@]}"; do
            if [ -f "$gov_dir/$doc" ]; then
                echo "    $doc"
            else
                echo "    $doc - MISSING"
                warnings=$((warnings + 1))
            fi
        done
    else
        echo "  Documents:   DIRECTORY NOT FOUND"
        warnings=$((warnings + 1))
    fi

    echo ""

    echo "TOOLS"
    echo "───────────────────────────────────────"

    local tools=("php" "node" "npm" "composer" "git" "python" "perl")
    for tool in "${tools[@]}"; do
        if command -v "$tool" > /dev/null 2>&1; then
            local version
            version="$($tool --version 2>&1 | head -1 | cut -c1-40)"
            echo "  $tool:        ${version}"
        else
            echo "  $tool:        not found"
        fi
    done

    echo ""
    echo "═══════════════════════════════════════"

    if [ $errors -eq 0 ] && [ $warnings -eq 0 ]; then
        echo "  HEALTHY - No issues found"
    elif [ $errors -eq 0 ]; then
        echo "  WARNINGS: $warnings"
    else
        echo "  ERRORS: $errors, WARNINGS: $warnings"
    fi

    echo "═══════════════════════════════════════"
    echo ""
}

main() {
    doctor_main "$@"
}
