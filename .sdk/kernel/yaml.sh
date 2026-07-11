#!/data/data/com.termux/files/usr/bin/bash

# Read a simple key: value from YAML
yaml_get() {
    local file="$1"
    local key="$2"
    if [ ! -f "$file" ]; then
        return 1
    fi
    grep "^[[:space:]]*${key}:" "$file" | head -1 | sed "s/^[[:space:]]*${key}:[[:space:]]*//"
}

# Write a key: value to YAML (updates if exists, appends if not)
yaml_set() {
    local file="$1"
    local key="$2"
    local value="$3"
    if [ ! -f "$file" ]; then
        return 1
    fi
    if grep -q "^[[:space:]]*${key}:" "$file"; then
        # Update existing key preserving indentation
        sed -i "s|^\([[:space:]]*${key}:\).*|\1 ${value}|" "$file"
    else
        echo "${key}: ${value}" >> "$file"
    fi
}

# Read nested YAML value (within a parent section)
yaml_get_nested() {
    local file="$1"
    local parent="$2"
    local child="$3"
    if [ ! -f "$file" ]; then
        return 1
    fi
    sed -n "/^${parent}:/,/^[a-z]/p" "$file" | grep "^[[:space:]]*${child}:" | head -1 | sed "s/^[[:space:]]*${child}:[[:space:]]*//"
}

# Check if key exists
yaml_has() {
    local file="$1"
    local key="$2"
    [ -f "$file" ] && grep -q "^[[:space:]]*${key}:" "$file"
}

# Validate YAML syntax (basic check)
yaml_validate() {
    local file="$1"
    if [ ! -f "$file" ]; then
        echo "File not found: $file"
        return 1
    fi
    # Check for common YAML errors
    if grep -n "^[[:space:]]*[^[:space:]#-].*:.*:" "$file" | grep -v "^[[:space:]]*#" >/dev/null 2>&1; then
        # Multiple colons on one line may indicate issues
        return 1
    fi
    if grep -n $'\t' "$file" >/dev/null 2>&1; then
        echo "Tabs found in YAML (use spaces): $file"
        return 1
    fi
    return 0
}

# Count items in a YAML list
yaml_count() {
    local file="$1"
    local parent="$2"
    if [ ! -f "$file" ]; then
        echo "0"
        return
    fi
    sed -n "/^${parent}:/,/^[a-z]/p" "$file" | grep -c "^[[:space:]]*- "
}
