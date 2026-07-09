
[2026-07-09 07:00] Claude (Debugger) — RESOLVED:
- Stale route cache caused /auth/login-pin to 404 despite being correctly defined in routes/api.php. Fix: php artisan route:clear + config:clear + cache:clear.
- Duplicate route registration found: app/Providers/RouteServiceProvider.php was manually loading routes/api.php with prefix('api/v1') on top of bootstrap/app.php's automatic api/ prefix + routes/api.php's own prefix('v1') group, producing api/v1/v1/... duplicates. Fix: removed the redundant $this->routes() block from RouteServiceProvider::boot(), kept both RateLimiter::for() definitions intact.
- Frontend: stale Vite dev server (started before .env existed) caused confirm-phone fetch to hit relative "undefined/..." path, returning Vite's HTML SPA fallback instead of JSON. Fix: restart Vite dev server after any .env change.
- Added debug JSON-parse-failure alert() to PhoneEntryScreenV2.jsx handleContinue for future non-JSON response diagnosis (safe to leave in, or strip once stable).
- RULE FOR ALL AGENTS: after any change to routes/api.php or any Providers/*.php file, run php artisan route:clear && config:clear before testing. A route existing in source but 404ing at runtime is almost always a stale cache, not a code bug — check this before touching frontend code.
