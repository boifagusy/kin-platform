# TERMUX STANDARDS

STATUS:
FROZEN

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

TERMUX FIRST

Assume:

- Android
- Termux
- Mobile Browser

Unless explicitly told otherwise.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

COMMAND FORMAT

Every implementation must provide:

BACKUP

PATCH

VERIFY

EXPECTED RESULT

ROLLBACK

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

NO MANUAL EDITING

Avoid:

nano
vim
manual editing

Prefer:

cat

sed

grep

find

cp

mv

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

FILE CREATION

Preferred:

cat > file << 'EOF'

content

