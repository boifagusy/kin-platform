#!/data/data/com.termux/files/usr/bin/bash

set -e

echo "========================================"
echo "KIN GOVERNANCE AUDIT REPORT"
echo "========================================"
echo

echo "DATE:"
date
echo

echo "PROJECT:"
pwd
echo

echo "========================================"
echo "GIT STATUS"
echo "========================================"
git status --short
echo

echo "CURRENT BRANCH:"
git branch --show-current
echo

echo "LAST 10 COMMITS"
git log --oneline -10
echo

echo "========================================"
echo "PROJECT TREE"
echo "========================================"

find . \
-maxdepth 2 \
\( -name frontend -o -name backend -o -name docs -o -name scripts -o -name backup \)

echo

echo "========================================"
echo "GOVERNANCE FILES"
echo "========================================"

find docs/governance -maxdepth 1 -type f | sort

echo

echo "========================================"
echo "SCRIPT FILES"
echo "========================================"

find scripts -maxdepth 1 -type f | sort

echo

echo "========================================"
echo "AGENT LOG"
echo "========================================"

cat docs/governance/AGENT_LOG.md

echo

echo "========================================"
echo "PROJECT DNA"
echo "========================================"

cat docs/governance/PROJECT_DNA.yaml

echo

echo "========================================"
echo "BUILD TEST"
echo "========================================"

cd frontend

npm run build

echo

echo "BUILD EXIT CODE: $?"

cd ..

echo

echo "========================================"
echo "LARAVEL ROUTES"
echo "========================================"

cd backend

php artisan route:list >/dev/null

echo "Route Check Exit Code: $?"

cd ..

echo

echo "========================================"
echo "END OF REPORT"
echo "========================================"

