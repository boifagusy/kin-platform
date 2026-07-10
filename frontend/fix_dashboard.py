#!/usr/bin/env python3
import os

file_path = os.path.expanduser("\~/kin_project/frontend/src/screens/ui-polish/DashboardScreenV2.jsx")

print("Looking for file at:", file_path)

if not os.path.exists(file_path):
    print("❌ File not found!")
    print("Current dir:", os.getcwd())
    exit(1)

with open(file_path, 'r') as f:
    content = f.read()

# Fix broken patterns
content = content.replace(r'`\( {API_BASE}/dashboard?phone= \){encodeURIComponent(phone)}`', r'`\( {API_BASE}/dashboard?phone= \){encodeURIComponent(phone)}`')
content = content.replace(r'`\\( {API_BASE}/dashboard?phone= \\){encodeURIComponent(phone)}`', r'`\( {API_BASE}/dashboard?phone= \){encodeURIComponent(phone)}`')

with open(file_path, 'w') as f:
    f.write(content)

print("✅ Fixed successfully!")
print("Run: npm run build")
