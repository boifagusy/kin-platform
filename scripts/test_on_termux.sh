#!/bin/bash

echo "═══════════════════════════════════════════════════════════════"
echo "  📱 KIN ANDROID TESTING ON TERMUX"
echo "═══════════════════════════════════════════════════════════════"
echo ""

cd ~/storage/kin_platform/frontend

# Build
echo "1️⃣ Building APK..."
npm run build && npx cap sync android && cd android && ./gradlew assembleDebug
cd ..

if [ ! -f "android/app/build/outputs/apk/debug/app-debug.apk" ]; then
    echo "❌ APK not found"
    exit 1
fi

echo "✅ APK built successfully"

# Install
echo ""
echo "2️⃣ Installing APK..."
adb install -r android/app/build/outputs/apk/debug/app-debug.apk

# Launch
echo ""
echo "3️⃣ Launching app..."
adb shell am start -n com.kin.app/.MainActivity

# View logs
echo ""
echo "4️⃣ Viewing logs (Ctrl+C to stop)..."
adb logcat -s "KIN" "Capacitor" "MainActivity" "KinLocationService" "KinLocationPlugin" "KinSafetyPlugin"
