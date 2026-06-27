#!/data/data/com.termux/files/usr/bin/bash

# KIN Android APK Builder for Termux
# Usage: ./scripts/build-apk-termux.sh

set -e

echo "🔨 KIN Android APK Builder (Termux)"
echo "====================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Check available memory
echo -e "${BLUE}📊 Checking system resources...${NC}"
FREE_RAM=$(free -m | awk '/^Mem:/{print $7}')
echo "   Available RAM: ${FREE_RAM}MB"

if [ $FREE_RAM -lt 512 ]; then
    echo -e "${YELLOW}⚠️  Low memory detected. Creating swap file...${NC}"
    cd ~
    if [ ! -f swapfile ]; then
        dd if=/dev/zero of=swapfile bs=1M count=2048 2>/dev/null
        chmod 600 swapfile
        mkswap swapfile 2>/dev/null
        swapon swapfile 2>/dev/null
        echo -e "${GREEN}✅ Swap file created (2GB)${NC}"
    fi
fi

# Step 1: Build web app
echo -e "${BLUE}📦 Step 1: Building web app...${NC}"
cd ~/storage/kin_platform/frontend
npm run build
echo -e "${GREEN}✅ Web build complete${NC}"

# Step 2: Sync Capacitor
echo -e "${BLUE}🔄 Step 2: Syncing Capacitor...${NC}"
npx cap sync android
echo -e "${GREEN}✅ Capacitor sync complete${NC}"

# Step 3: Build APK
echo -e "${BLUE}📱 Step 3: Building APK...${NC}"
cd android

# Stop any existing Gradle daemons
./gradlew --stop 2>/dev/null || true

# Clean build (optional, removes old builds)
echo -e "${YELLOW}🧹 Cleaning previous builds...${NC}"
./gradlew clean --no-daemon --max-workers=1 2>/dev/null || true

# Build with memory optimization
echo -e "${BLUE}🔨 Building APK (this may take 5-10 minutes)...${NC}"
./gradlew assembleDebug \
    --no-daemon \
    --max-workers=1 \
    --no-parallel \
    -Dorg.gradle.jvmargs="-Xmx512m -XX:MaxMetaspaceSize=256m"

echo -e "${GREEN}✅ APK build complete!${NC}"

# Step 4: Show APK location
echo ""
echo -e "${GREEN}🎉 BUILD SUCCESSFUL!${NC}"
echo ""
echo -e "${BLUE}📁 APK Location:${NC}"
APK_PATH="app/build/outputs/apk/debug/app-debug.apk"
ls -lh "$APK_PATH" 2>/dev/null || echo "   APK not found at expected location"
echo ""
echo -e "${YELLOW}📦 To install the APK:${NC}"
echo "   adb install $APK_PATH"
echo "   OR open with file manager and tap to install"
echo ""

# Optional: Open APK in file manager
echo -e "${BLUE}📂 Open APK in file manager? (y/n)${NC}"
read -r answer
if [[ "$answer" =~ ^[Yy]$ ]]; then
    # Use termux-open to open the APK
    termux-open "$APK_PATH"
fi

echo -e "${GREEN}✅ Done!${NC}"
