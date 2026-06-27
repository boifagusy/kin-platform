#!/bin/bash

# Restore script for KIN backup
BACKUP_DIR="$(dirname "$0")"
echo "🔄 Restoring files from: $BACKUP_DIR"
echo ""

# Restore frontend files
cp "$BACKUP_DIR/router.jsx.backup" frontend/src/router.jsx 2>/dev/null && echo "✅ router.jsx"
cp "$BACKUP_DIR/App.jsx.backup" frontend/src/App.jsx 2>/dev/null && echo "✅ App.jsx"
cp "$BACKUP_DIR/main.jsx.backup" frontend/src/main.jsx 2>/dev/null && echo "✅ main.jsx"
cp "$BACKUP_DIR/index.css.backup" frontend/src/index.css 2>/dev/null && echo "✅ index.css"
cp "$BACKUP_DIR/vite.config.js.backup" frontend/vite.config.js 2>/dev/null && echo "✅ vite.config.js"
cp "$BACKUP_DIR/.env.backup" frontend/.env 2>/dev/null && echo "✅ frontend .env"
cp "$BACKUP_DIR/api.js.backup" frontend/src/services/api.js 2>/dev/null && echo "✅ api.js"
cp "$BACKUP_DIR/sosService.js.backup" frontend/src/services/sosService.js 2>/dev/null && echo "✅ sosService.js"
cp "$BACKUP_DIR/secureStorage.js.backup" frontend/src/services/secureStorage.js 2>/dev/null && echo "✅ secureStorage.js"
cp "$BACKUP_DIR/useSilentSOS.js.backup" frontend/src/hooks/useSilentSOS.js 2>/dev/null && echo "✅ useSilentSOS.js"
cp "$BACKUP_DIR/WelcomeScreenV3.jsx.backup" frontend/src/screens/ui-polish/WelcomeScreenV3.jsx 2>/dev/null && echo "✅ WelcomeScreenV3.jsx"
cp "$BACKUP_DIR/PhoneEntryScreenV2.jsx.backup" frontend/src/screens/ui-polish/PhoneEntryScreenV2.jsx 2>/dev/null && echo "✅ PhoneEntryScreenV2.jsx"
cp "$BACKUP_DIR/LoginPinScreenV2.jsx.backup" frontend/src/screens/ui-polish/LoginPinScreenV2.jsx 2>/dev/null && echo "✅ LoginPinScreenV2.jsx"

# Restore backend files
cp "$BACKUP_DIR/api.php.backup" backend/routes/api.php 2>/dev/null && echo "✅ api.php"
cp "$BACKUP_DIR/AuthController.php.backup" backend/app/Http/Controllers/Api/V1/AuthController.php 2>/dev/null && echo "✅ AuthController.php"
cp "$BACKUP_DIR/SosController.php.backup" backend/app/Http/Controllers/Api/V1/SosController.php 2>/dev/null && echo "✅ SosController.php"
cp "$BACKUP_DIR/DuressPinController.php.backup" backend/app/Http/Controllers/Api/V1/DuressPinController.php 2>/dev/null && echo "✅ DuressPinController.php"
cp "$BACKUP_DIR/IncidentController.php.backup" backend/app/Http/Controllers/Api/V1/IncidentController.php 2>/dev/null && echo "✅ IncidentController.php"
cp "$BACKUP_DIR/User.php.backup" backend/app/Models/User.php 2>/dev/null && echo "✅ User.php"
cp "$BACKUP_DIR/SafetyScoreService.php.backup" backend/app/Services/SafetyScoreService.php 2>/dev/null && echo "✅ SafetyScoreService.php"
cp "$BACKUP_DIR/SendSosAlertJob.php.backup" backend/app/Jobs/SendSosAlertJob.php 2>/dev/null && echo "✅ SendSosAlertJob.php"
cp "$BACKUP_DIR/.env.backup" backend/.env 2>/dev/null && echo "✅ backend .env"

echo ""
echo "✅ Restore complete!"
echo ""
echo "🔄 Restart both servers:"
echo "   cd frontend && pkill -f vite && npm run dev"
echo "   cd backend && pkill -f php && php artisan serve"
