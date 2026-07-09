#!/data/data/com.termux/files/usr/bin/bash

echo
echo "======================================="
echo " KIN START SESSION"
echo "======================================="

./scripts/ai-bootstrap.sh

echo
./scripts/pre-patch-check.sh

echo
echo "✅ Ready to begin development."
