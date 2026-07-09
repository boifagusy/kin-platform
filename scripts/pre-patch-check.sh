#!/data/data/com.termux/files/usr/bin/bash

echo "========================================"
echo "PRE-PATCH VALIDATION"
echo "========================================"

echo
echo "[1] Repository"
pwd
git rev-parse --is-inside-work-tree || exit 1

echo
echo "[2] Branch"
git branch --show-current

echo
echo "[3] Git Status"
git status --short

echo
echo "[4] Active Agent"
tail -20 docs/governance/AGENT_LOG.md

echo
echo "[5] Governance"
find docs/governance -maxdepth 1 -type f | sort

echo
echo "READY FOR PATCH"
