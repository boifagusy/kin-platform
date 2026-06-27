#!/bin/bash

cd ~/storage/kin_platform/backend

echo "=========================================="
echo "  SAFE MIGRATION CLEANUP"
echo "=========================================="

# Step 1: Check current status
echo ""
echo "Step 1: Checking migration status..."
php artisan migrate:status > /tmp/migrate_status.txt
cat /tmp/migrate_status.txt | head -30

# Step 2: Create archive folder
echo ""
echo "Step 2: Creating archive folder..."
mkdir -p database/migrations/archive

# Step 3: Move duplicate migrations (WITHOUT deleting)
echo ""
echo "Step 3: Moving duplicate migrations to archive..."

# Check for duplicates and move them safely
for file in $(ls database/migrations/*.php 2>/dev/null); do
    # Check if this file has a duplicate
    base=$(basename "$file")
    
    # Skip if already in archive
    if [[ "$base" == *"archive"* ]]; then
        continue
    fi
    
    # Check for duplicates by table name
    if grep -q "create_safety_incidents_table" "$file" 2>/dev/null; then
        # Check if another file also creates this table
        count=$(grep -l "create_safety_incidents_table" database/migrations/*.php 2>/dev/null | wc -l)
        if [ "$count" -gt 1 ]; then
            echo "Moving duplicate: $base"
            mv "$file" database/migrations/archive/
        fi
    fi
    
    if grep -q "create_incident_notifications_table" "$file" 2>/dev/null; then
        count=$(grep -l "create_incident_notifications_table" database/migrations/*.php 2>/dev/null | wc -l)
        if [ "$count" -gt 1 ]; then
            echo "Moving duplicate: $base"
            mv "$file" database/migrations/archive/
        fi
    fi
    
    if grep -q "add_user_id_to_safety_incidents" "$file" 2>/dev/null; then
        count=$(grep -l "add_user_id_to_safety_incidents" database/migrations/*.php 2>/dev/null | wc -l)
        if [ "$count" -gt 1 ]; then
            echo "Moving duplicate: $base"
            mv "$file" database/migrations/archive/
        fi
    fi
done

# Step 4: Verify what's left
echo ""
echo "Step 4: Remaining migrations:"
ls -la database/migrations/*.php 2>/dev/null | wc -l
echo "migration files remaining"

# Step 5: Verify status again
echo ""
echo "Step 5: Verifying migration status after cleanup..."
php artisan migrate:status

echo ""
echo "=========================================="
echo "  CLEANUP COMPLETE"
echo "=========================================="
echo ""
echo "✅ Duplicates moved to: database/migrations/archive/"
echo "✅ To restore: mv database/migrations/archive/*.php database/migrations/"
echo "✅ To fully remove: rm -rf database/migrations/archive/"
