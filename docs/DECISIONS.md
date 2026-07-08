# KIN ARCHITECTURE DECISIONS

This document records permanent architectural decisions.

==================================================

## DECISION-001

Title:
Primary Location Provider

Decision:

Capacitor Geolocation is the primary provider.

Fallback:

navigator.geolocation

Native:

Android FusedLocationProviderClient

Status:

APPROVED

==================================================

## DECISION-002

Title:
Offline Location Queue

Decision:

IndexedDB is the storage engine.

Reason:

• Built into browsers
• Works in Capacitor
• Large storage
• Structured queries
• No additional dependency

Status:

APPROVED

==================================================

## DECISION-003

Title:
Documentation History

Decision:

Git is the only history system.

Never duplicate documents.

Update the living document instead.

Status:

APPROVED

==================================================

## DECISION-004

Title:
Discovery Policy

Decision:

Every implementation must:

1. Bootstrap
2. Search repository
3. Read code
4. Review Git
5. Produce Discovery Report

Status:

APPROVED

