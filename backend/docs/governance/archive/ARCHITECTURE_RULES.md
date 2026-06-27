# ARCHITECTURE RULES

STATUS: ACTIVE

CONTROLLER

Only:

- Validation
- Authorization
- Service calls

No business logic.

SERVICE

Contains:

- Business logic
- Workflows
- Rules

MODEL

Contains:

- Relationships
- Scopes
- Casts

VIEW

Contains:

- Presentation only

REUSE ORDER

REUSE
↓
EXTEND
↓
REFACTOR
↓
CREATE

Never duplicate architecture.

