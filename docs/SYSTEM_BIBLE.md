# KIN PLATFORM — SYSTEM BIBLE

**Version:** 2.0  
**Last Updated:** 2026-07-04  
**Status:** Production Stable

## Core Subsystems

| Subsystem | Version | Status | Purpose |
|-----------|---------|--------|---------|
| Guardian | v0.1 | ✅ Active | Safety monitoring |
| Pulse | v0.4 | ✅ Active | Health tracking |
| Recovery | v1.0 | ✅ Active | Incident recovery |
| Sentinel | v0.5 | ✅ Active | Security |
| Watchtower | v2.0 | ✅ Active | System health |

## Watchtower v2.0

### Health Service
- getSystemHealth() - Returns health data with fallbacks
- checkServices() - Checks all system services
- Safe system load reading
- Never fails, always returns data

### Routes
- /admin/watchtower/overview - Main dashboard
- /admin/watchtower/health - System health page

### Recent Updates (2026-07-04)
- Fixed sidebar HTML structure
- Removed duplicate System Health entries
- HealthService with robust fallback system

## Quick Links
- Dashboard: http://127.0.0.1:8000/admin/dashboard
- Watchtower: http://127.0.0.1:8000/admin/watchtower/overview
- System Health: http://127.0.0.1:8000/admin/watchtower/health
