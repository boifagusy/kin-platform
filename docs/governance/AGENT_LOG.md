# KIN AGENT LOG

Purpose: Cross-session coordination and engineering history.

---

# SESSION HISTORY

## SESSION END — 2026-07-09 07:00

**Agent:** Claude

**Role:** Debugger

**Objective**
Restore Login PIN flow and eliminate duplicate API routing.

### Root Causes

1. Stale Laravel route cache caused `/auth/login-pin` to return 404 although the route existed.

**Fix**
- php artisan route:clear
- php artisan config:clear
- php artisan cache:clear

---

2. Duplicate API prefixes.

Problem:

```
bootstrap/app.php
        +
RouteServiceProvider
        +
routes/api.php
```

Result:

```
api/v1/v1/...
```

**Fix**

Removed the manual `$this->routes()` registration from:

```
app/Providers/RouteServiceProvider.php
```

Kept RateLimiter configuration intact.

---

3. Stale Vite dev server

Problem:

Frontend started before `.env` existed.

Result:

```
fetch("undefined/...")
```

Vite returned HTML instead of JSON.

**Fix**

Restart Vite after any `.env` change.

---

4. Debug improvement

Added temporary JSON parse failure alert in:

```
PhoneEntryScreenV2.jsx
```

Safe to remove once the project is stable.

---

## Permanent Engineering Rule

After changing any of these:

- routes/api.php
- app/Providers/*
- config/*
- .env

Always run:

```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

If a route exists in source but returns **404**, assume **stale cache first**, not a frontend bug.

---

## Verification

Backend routes restored.

Frontend login flow restored after Vite restart.

Status: CLOSED
