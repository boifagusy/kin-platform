#!/data/data/com.termux/files/usr/bin/bash

echo
echo "======================================="
echo " KIN FINISH SESSION"
echo "======================================="

./scripts/post-patch-check.sh

echo
./scripts/full-backup.sh

echo
./scripts/cleanup-governance.sh

echo
echo "✅ Session complete."
