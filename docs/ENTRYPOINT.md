# KIN PLATFORM — ENTRYPOINT

Version: 4.0.0
Status: ACTIVE
Purpose: Master startup document for all AI agents and contributors.

---

## PURPOSE

This file is the mandatory starting point for all work on KIN.

Before any analysis, planning, coding, testing, documentation, or release activity, every agent must read the required documents in the order defined below.

No agent may skip this process.

---

## SOURCE OF TRUTH

Priority Order:

1. Running System
2. Database Schema
3. Routes
4. Source Code
5. Test Results
6. Documentation
7. AI Reports
8. AI Assumptions

If documentation conflicts with code:

CODE WINS.

Documentation must be updated.

Never modify code to match outdated documentation.

---

## DOCUMENT LOADING ORDER

Every agent must read:

1. PROJECT_STATUS.md
2. PROJECT_DNA.yaml
3. SYSTEM_BIBLE.md
4. BRICK_REGISTRY.yaml
5. CODEMAP.md
6. GOVERNANCE.md

Only after these documents are reviewed may work begin.

---

## BRICK LIFECYCLE

Every feature, bug fix, enhancement, or architectural change is represented as a Brick.

Mandatory lifecycle:

IDEA
↓
DISCOVERING
↓
VERIFIED
↓
ARCHITECTURE
↓
BUILD PLAN
↓
BUILDING
↓
PATCHED
↓
TESTING
↓
USER VALIDATED
↓
FROZEN
↓
DOCUMENTED

Rules:

- AI may not mark VALIDATED.
- AI may not mark FROZEN.
- Only Project Owner may approve VALIDATED and FROZEN.

---

## AGENT ARCHITECTURE

Project Owner
│
├── Product Agent
├── Design Agent
├── Build Agent
├── QA Agent
└── Release Agent

All agents work from the same documentation system.

No agent may create its own source of truth.

---

## AGENT RESPONSIBILITIES

### Product Agent

Responsibilities:

- Define requirements
- Define bricks
- Create specifications
- Verify business logic
- Maintain roadmap alignment

Primary Documents:

- PROJECT_STATUS.md
- SYSTEM_BIBLE.md

Outputs:

- Requirements
- Specifications
- Brick Definitions

---

### Design Agent

Responsibilities:

- UX flows
- Wireframes
- UI systems
- Design decisions

Primary Documents:

- SYSTEM_BIBLE.md
- Product Specifications

Outputs:

- Design documents
- User flows

---

### Build Agent

Responsibilities:

- Frontend
- Backend
- APIs
- Database implementation

Primary Documents:

- BRICK_REGISTRY.yaml
- CODEMAP.md
- SYSTEM_BIBLE.md

Outputs:

- Working implementation

---

### QA Agent

Responsibilities:

- Validation
- Testing
- Security review
- Performance review
- Production simulation

Primary Documents:

- Specifications
- Build output

Outputs:

- QA reports
- Validation reports

---

### Release Agent

Responsibilities:

- Documentation updates
- Build logs
- Release notes
- Governance synchronization

Primary Documents:

- BUILD_LOG.md
- PROJECT_STATUS.md

Outputs:

- Updated documentation
- Release records

---

## AGENT REVIEW PROTOCOL

Agents may request reviews from:

- ChatGPT
- Claude
- Gemini
- DeepSeek
- Other approved AI systems

Review Format:

Reviewer:
Purpose:
Score:
Findings:
Recommendations:

External reviews are advisory.

Project documentation remains the source of truth.

---

## DOCUMENT STRUCTURE

docs/

ENTRYPOINT.md

PROJECT_STATUS.md

PROJECT_DNA.yaml

SYSTEM_BIBLE.md

CODEMAP.md

GOVERNANCE.md

BUILD_LOG.md

BRICK_REGISTRY.yaml

architecture/
└── MASTER_ROADMAP_DASHBOARD.md

---

## REUSE RULE

Before creating:

- Service
- Controller
- Component
- Route
- Model
- Migration
- Screen

Mandatory order:

REUSE
↓
EXTEND
↓
REFACTOR
↓
CREATE

Duplicate systems are prohibited.

---

## TERMUX STANDARD

KIN is developed primarily using:

- Android
- Termux
- Mobile Browser

Detailed commands and workflows are defined in GOVERNANCE.md.

---

## CURRENT PROJECT OBJECTIVE

Build KIN using a Brick-based architecture that:

- Prevents duplication
- Prevents documentation drift
- Supports multi-agent development
- Supports external AI review
- Remains maintainable for 5+ years

---

## FINAL RULE

Before any action ask:

1. What brick are we working on?
2. What phase is it in?
3. What evidence exists?
4. What can be reused?
5. Which documents must be updated?
6. Does this align with the architecture?

If any answer is unknown:

Return to DISCOVERY.
