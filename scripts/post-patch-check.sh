#!/data/data/com.termux/files/usr/bin/bash

echo "========================================"
echo "POST PATCH VALIDATION"
echo "========================================"

echo
echo "Git Status"
git status --short

echo
echo "Frontend Build"

cd frontend || exit 1
npm run build

echo
echo "EXIT CODE: $?"

cd ..

echo
echo "Recent Commit"
git log --oneline -3

echo
echo "Validation Complete"
