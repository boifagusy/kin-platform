# KIN Watchtower Observability System

## Overview
Production-grade monitoring system for the KIN platform.

## Modules
1. System Health - API, database, cache, storage, queue, scheduler
2. API Monitor - Response times, error rates, top endpoints
3. Queue Monitor - Queue size, failed jobs, stuck jobs
4. Database Monitor - Connection, migrations, tables
5. Plugin Health - All Capacitor plugins
6. Safety Engine Monitor - Check-ins, SOS, duress
7. Performance Monitor - CPU, memory, storage, API latency
8. Error Monitor - Laravel, API, network, offline failures
9. Notification Monitor - SMS, push, email delivery
10. Security Monitor - Failed logins, JWT failures, rate limits

## Endpoints
All endpoints require admin authentication.

| Endpoint | Description |
|----------|-------------|
| /api/v1/health | Public health check |
| /api/watchtower/health | Full system health |
| /api/watchtower/api/metrics | API performance |
| /api/watchtower/queue/metrics | Queue status |
| /api/watchtower/database/metrics | Database health |
| /api/watchtower/plugins | Plugin health |
| /api/watchtower/safety/metrics | Safety engine |
| /api/watchtower/performance/metrics | System performance |
| /api/watchtower/errors/metrics | Error tracking |
| /api/watchtower/notifications/metrics | Notification delivery |
| /api/watchtower/security/metrics | Security metrics |

## Version
1.0.0
