#!/data/data/com.termux/files/usr/bin/bash

STAMP=$(date +%Y%m%d_%H%M%S)

echo "Creating backup..."

rm -rf backup/latest

mkdir -p backup/latest

cp -r frontend backup/latest/
cp -r backend backup/latest/
cp -r docs backup/latest/

echo

echo "Creating release snapshot..."

mkdir -p backup/releases

cp -r backup/latest backup/releases/release_$STAMP

echo
echo "Backup Complete"
echo "$STAMP"
