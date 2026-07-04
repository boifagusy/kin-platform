# KIN PLATFORM — CODEMAP

**Last Updated:** 2026-07-04

## Controllers
- WatchtowerController@overview → /admin/watchtower/overview
- WatchtowerController@health → /admin/watchtower/health (v2.0)

## Services
- HealthService (v2.0) - System health with fallbacks
  - getSystemHealth()
  - checkServices()
  - getSystemLoadWithFallback()

## Views
- watchtower/overview.blade.php
- watchtower/health.blade.php (v2.0)

## Partials
- admin/sidebar.blade.php (Fixed v2.0)
