#!/data/data/com.termux/files/usr/bin/bash

clear

cd ~/kin_project || exit 1

echo "=========================================="
echo "          KIN AI BOOTSTRAP"
echo "=========================================="

echo
echo "Repository"
pwd

echo
echo "Current Branch"
git branch --show-current

echo
echo "Git Status"
git status

echo
echo "Diff Summary"
git --no-pager diff --stat

echo
echo "Recent Commits"
git --no-pager log --oneline -10

echo
echo "Open Sessions"

if [ ! -f AGENT_LOG.md ]; then
    echo "No AGENT_LOG.md found."
else
    if grep -q "Status: ACTIVE" AGENT_LOG.md; then
        awk '
        BEGIN {
            RS="---"
        }
        /Status: ACTIVE/ {
            last=$0
        }
        END {
            if (last != "")
                print last
            else
                print "No active sessions."
        }
        ' AGENT_LOG.md
    else
        echo "No active sessions."
    fi
fi

echo
echo "Core Documents"

for f in \
docs/00_START_HERE.md \
docs/AI_CONTEXT.md \
docs/PROJECT_STATUS.md \
docs/FEATURE_REGISTRY.md \
docs/CODEMAP.md \
docs/PROTECTED_FILES.md \
docs/KIN_RUNBOOK.md \
docs/DECISIONS.md
do
    if [ -f "$f" ]; then
        echo "✓ $f"
    else
        echo "✗ Missing: $f"
    fi
done

echo
echo "Required Reading"
echo "1. AGENT_PROTOCOL.md"
echo "2. ROUTING_GUIDE.md"
echo "3. KIN_RUNBOOK.md"
echo "4. PROJECT_STATUS.md"
echo "5. DECISIONS.md"

echo
echo "=========================================="

echo
echo "=========================================="
echo "HOW TO USE THIS BOOTSTRAP"
echo "=========================================="
echo "1. Run this script before EVERY new AI session."
echo "2. Copy ALL output."
echo "3. Paste the output as the FIRST message to the AI."
echo "4. Then describe your task."
echo
echo "The AI MUST:"
echo "✓ Read AGENT_PROTOCOL.md"
echo "✓ Read ROUTING_GUIDE.md"
echo "✓ Check KIN_RUNBOOK.md"
echo "✓ Check DECISIONS.md"
echo "✓ Check PROJECT_STATUS.md"
echo "✓ Check AGENT_LOG.md"
echo "✓ Follow the protocol before editing code."
echo
echo "Never skip the bootstrap."
echo

echo
echo "=========================================="
echo "AI SELF-CHECK"
echo "=========================================="
echo "[ ] Bootstrap reviewed"
echo "[ ] AGENT_PROTOCOL.md reviewed"
echo "[ ] KIN_RUNBOOK.md reviewed"
echo "[ ] PROJECT_STATUS.md reviewed"
echo "[ ] DECISIONS.md reviewed"
echo "[ ] AGENT_LOG.md checked for ACTIVE sessions"
echo "[ ] Discovery completed before coding"
echo "[ ] Documentation will be updated if required"
echo "[ ] Session Completion Checklist must be completed"
echo "=========================================="

echo "Bootstrap Complete"
echo "Paste the output above into every AI session."
echo "=========================================="

