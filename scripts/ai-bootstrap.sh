#!/data/data/com.termux/files/usr/bin/bash

echo "========================================="
echo "      KIN AI BOOTSTRAP"
echo "========================================="
echo

echo "Project:"
pwd
echo

echo "Branch:"
git branch --show-current
echo

echo "Git Status:"
git status --short
echo

echo "Latest Commit:"
git log --oneline -1
echo

echo "========================================="
echo "ACTIVE AGENT SESSION"
echo "========================================="
if [ -f docs/governance/AGENT_LOG.md ]; then
    tail -25 docs/governance/AGENT_LOG.md
else
    echo "No AGENT_LOG.md found."
fi
echo

echo "========================================="
echo "PROJECT STATUS"
echo "========================================="
if [ -f docs/PROJECT_STATUS.md ]; then
    head -20 docs/PROJECT_STATUS.md
else
    echo "PROJECT_STATUS.md not found."
fi
echo

echo "========================================="
echo "GOVERNANCE FILES"
echo "========================================="
find docs/governance -maxdepth 1 -type f | sort
echo

echo "========================================="
echo "FRONTEND BUILD"
echo "========================================="
if [ -d frontend ]; then
    cd frontend
    npm run build >/tmp/kin_build.log 2>&1
    tail -20 /tmp/kin_build.log
    echo
    echo "Exit Code: $?"
    cd ..
else
    echo "Frontend not found."
fi

echo
echo "========================================="
echo "BOOTSTRAP COMPLETE"
echo "========================================="
echo
echo "Copy everything above into a new AI session."
