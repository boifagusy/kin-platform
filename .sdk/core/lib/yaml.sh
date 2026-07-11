#!/data/data/com.termux/files/usr/bin/bash

# Read a simple key: value from YAML
read_yaml() {
    local file="$1"
    local key="$2"
    if [ ! -f "$file" ]; then
        return 1
    fi
    grep "^[[:space:]]*${key}:" "$file" | head -1 | sed "s/^[[:space:]]*${key}:[[:space:]]*//"
}

# Write a key: value to YAML
write_yaml() {
    local file="$1"
    local key="$2"
    local value="$3"
    if [ ! -f "$file" ]; then
        return 1
    fi
    if grep -q "^[[:space:]]*${key}:" "$file"; then
        sed -i "s|^\([[:space:]]*${key}:\).*|\1 ${value}|" "$file"
    else
        echo "${key}: ${value}" >> "$file"
    fi
}

# Read nested YAML value (parent section, child key)
read_yaml_nested() {
    local file="$1"
    local parent="$2"
    local child="$3"
    if [ ! -f "$file" ]; then
        return 1
    fi
    sed -n "/^${parent}:/,/^[a-z]/p" "$file" | grep "^[[:space:]]*${child}:" | head -1 | sed "s/^[[:space:]]*${child}:[[:space:]]*//"
}

# Check if a key exists in YAML
yaml_key_exists() {
    local file="$1"
    local key="$2"
    grep -q "^[[:space:]]*${key}:" "$file" 2>/dev/null
}
