#!/data/data/com.termux/files/usr/bin/bash

echo "Cleaning..."

find . -name "*.backup*" -delete
find . -name "*.bak" -delete
find . -name "*.tmp" -delete

echo

echo "Remaining Backups"

find backup -maxdepth 2

echo

echo "Done."
