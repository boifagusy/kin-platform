#!/bin/bash

echo "📱 GATE 5: DEVICE VALIDATION"
echo "============================"
echo ""

cd ~/storage/kin_platform/frontend

# Check for connected devices
echo "1️⃣ Checking connected devices..."
ADB_DEVICES=$(adb devices | grep -v "List" | grep -v "^$" | wc -l)
if [ "$ADB_DEVICES" -gt 0 ]; then
    echo "✅ $ADB_DEVICES device(s) connected"
    adb devices
else
    echo "⚠️ No devices connected"
    echo "   Please connect a device or start an emulator"
fi

echo ""
echo "2️⃣ Checking Android SDK..."
if [ -d "$ANDROID_HOME" ] || [ -d "/data/data/com.termux/files/usr/lib/android-sdk" ]; then
    echo "✅ Android SDK found"
else
    echo "❌ Android SDK not found"
fi

echo ""
echo "3️⃣ Device validation scenarios..."
echo ""
echo "┌─────────────────────────────────────────────────────────────┐"
echo "│  Device Validation Scenarios                               │"
echo "├─────────────────────────────────────────────────────────────┤"
echo "│  1. Installation      │ APK installs correctly             │"
echo "│  2. Permissions       │ All permissions granted            │"
echo "│  3. Authentication    │ Phone + PIN flow works             │"
echo "│  4. Location          │ GPS and network location           │"
echo "│  5. Check-in          │ Online and offline                 │"
echo "│  6. SOS               │ Emergency trigger + contacts       │"
echo "│  7. Notifications     │ Local + push notifications         │"
echo "│  8. Background        │ Service runs in background         │"
echo "│  9. Battery           │ Consumption acceptable             │"
echo "│ 10. Performance       │ App responsiveness                 │"
echo "└─────────────────────────────────────────────────────────────┘"

echo ""
echo "4️⃣ Generating device test report..."
cat > ~/storage/kin_platform/docs/device_validation_report.md << 'DOC'
# Device Validation Report

## Test Environment
- **Date**: $(date)
- **App Version**: 1.0
- **Build**: Debug/Release

## Devices Tested

| Device | Model | Android Version | Status |
|--------|-------|-----------------|--------|
| Google Pixel 7 | Pixel 7 | 15 | PENDING |
| Samsung Galaxy S23 | S23 | 14 | PENDING |
| Tecno Camon 20 | Camon 20 | 13 | PENDING |
| Infinix Note 12 | Note 12 | 12 | PENDING |
| Xiaomi Redmi Note 11 | Redmi Note 11 | 12 | PENDING |

## Test Results

### Installation
- [ ] APK installs successfully
- [ ] App launches
- [ ] Splash screen displays

### Permissions
- [ ] Notification permission
- [ ] Location permission
- [ ] Background location permission

### Authentication
- [ ] Phone entry works
- [ ] OTP verification works
- [ ] PIN creation works
- [ ] PIN login works

### Location
- [ ] GPS location works
- [ ] Network location fallback works
- [ ] Background location works

### Check-in
- [ ] Online check-in works
- [ ] Offline check-in queues
- [ ] Sync works after reconnection

### SOS
- [ ] SOS trigger works
- [ ] Contacts receive notifications
- [ ] Location shared

### Notifications
- [ ] Local notifications work
- [ ] Push notifications work
- [ ] Notification actions work

### Background
- [ ] Service runs in background
- [ ] Battery optimization works
- [ ] Doze mode handling works

### Battery
- [ ] Usage < 5% per hour
- [ ] No excessive drain

### Performance
- [ ] Cold start < 2 seconds
- [ ] UI smoothness
- [ ] No ANR

## Issues Found

| Issue | Severity | Status |
|-------|----------|--------|
| None | - | - |

## Conclusion

**Device Validation Status:** PENDING
**Release Readiness:** NOT READY
**Action Required:** Complete device testing
DOC

echo "✅ Device validation report generated"
echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "  📱 GATE 5: DEVICE VALIDATION — READY"
echo "═══════════════════════════════════════════════════════════════"
echo ""
echo "To run device tests:"
echo "  1. Connect Android device via USB"
echo "  2. Build APK: cd frontend && npx cap build android"
echo "  3. Install APK: adb install app-debug.apk"
echo "  4. Run tests manually on each device"
echo ""
echo "═══════════════════════════════════════════════════════════════"
