# ENGINE API v1.0 — Standard Interface

## Required Commands
Every engine must expose:

| Command | Output | Exit Code |
|---------|--------|-----------|
| `ai <engine> help` | Usage text | 0 |
| `ai <engine> version` | Version string | 0 |
| `ai <engine> status` | Current state | 0 |
| `ai <engine> health` | Health status | 0=healthy, 1=unhealthy |
| `ai <engine> doctor` | Diagnostic report | 0 |
| `ai <engine> validate` | Validation result | 0=pass, 1=fail |

## Reference Implementation
See: gate, intelligence, project engines

## Compliance
Run: `ai engine compliance`
