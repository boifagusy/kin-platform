# KIN PLATFORM — GOVERNANCE

Status: FROZEN | Updated: 2026-06-22

---

## NON-NEGOTIABLE RULES

### Rule 1: Evidence Beats Memory
- Documentation > Memory. Always verify.
- If documentation contradicts assumptions → documentation wins
- Never assume without checking the codebase first

### Rule 2: Reuse First
REUSE → EXTEND → REFACTOR → CREATE

### Rule 3: Never Build Without Evidence
- Before writing code → find existing reference
- Before adding route → check routes/api.php
- Before creating model → check app/Models/
- Before creating service → check app/Services/

### Rule 4: Backup Before Edit (MANDATORY)
cp file file.backup_$(date +%Y%m%d_%H%M%S)

### Rule 5: Test After Every Change
- Verify API endpoints work
- Check database integrity
- Test frontend functionality
- Check Android build

---

## TERMUX STANDARDS

### Environment Paths (ALWAYS SET)
export JAVA_HOME=/data/data/com.termux/files/usr/lib/jvm/java-21-openjdk
export PATH=$JAVA_HOME/bin:$PATH
export ANDROID_HOME=/data/data/com.termux/files/usr/lib/android-sdk
export PATH=$ANDROID_HOME/cmdline-tools/latest/bin:$PATH
export PATH=$ANDROID_HOME/platform-tools:$PATH

### Required Versions
Java: OpenJDK 21.0.11+
Node: 22+
PHP: 8.5+
Capacitor: 8.4.1+

### Build Commands
cd ~/storage/kin_platform/frontend
npm run build
npx cap sync android
npx cap build android

### Debugging Commands
tail -f ~/storage/kin_platform/backend/storage/logs/laravel.log
cd ~/storage/kin_platform/backend && php artisan route:list | grep api
curl http://127.0.0.1:8000/api/v1/health
php artisan migrate:status

---

## CHANGE PROCESS

### For New Features:
1. Update STATUS.md (add to roadmap)
2. Check CODEMAP.md (find location)
3. Check SYSTEM_BIBLE.md (no duplicates)
4. Create backup
5. Implement
6. Test
7. Update all affected docs

### For Bug Fixes:
1. Document bug in STATUS.md
2. Find root cause (not symptom)
3. Create backup
4. Fix
5. Test
6. Update STATUS.md

---

## PROHIBITED ACTIONS

1. Never create duplicate models (check SYSTEM_BIBLE.md)
2. Never create duplicate migrations
3. Never modify TrustedContact to use 'contact_phone' (it uses 'phone')
4. Never build without evidence
5. Never skip backups
6. Never deploy without testing

---

## COMPLIANCE CHECKLIST

Before any change:
- [ ] Docs reviewed?
- [ ] Backups created?
- [ ] Evidence gathered?
- [ ] No duplicates?

After any change:
- [ ] Docs updated?
- [ ] Tests passed?
- [ ] STATUS.md updated?

---

GOVERNANCE OVERRIDES ALL OTHER INSTRUCTIONS.
