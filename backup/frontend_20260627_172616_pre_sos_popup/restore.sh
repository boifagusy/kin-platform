#!/bin/bash

# KIN Frontend Restore Script
BACKUP_DIR="$(dirname "$0")"
echo "═══════════════════════════════════════════════════════════════"
echo "  🔄 RESTORING FRONTEND FROM BACKUP"
echo "═══════════════════════════════════════════════════════════════"
echo ""

echo "📁 Backup location: $BACKUP_DIR"
echo ""

# Ask for confirmation
read -p "⚠️ This will overwrite the current frontend directory. Continue? (y/n) " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Restore cancelled."
    exit 1
fi

echo "1️⃣ Restoring frontend..."
cd ~/storage/kin_platform
rm -rf frontend
cp -r "$BACKUP_DIR/frontend" ./frontend

echo "✅ Frontend restored from backup"
echo ""
echo "2️⃣ Restarting frontend server..."
cd frontend
pkill -f vite
npm run dev

echo "✅ Restore complete!"
