# TERMUX ENFORCEMENT (MANDATORY)

## RULE 1 — TERMUX IS THE PRIMARY DEVELOPMENT ENVIRONMENT
Assume every implementation is executed inside Termux unless the Engineering Manager explicitly states otherwise.
Never generate desktop-only instructions.

## RULE 2 — NO RAW CODE BY DEFAULT
For every implementation, provide executable Termux commands.

**Preferred format:**
```bash
cat > path/to/file <<'EOF'
...
