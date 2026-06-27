# Duress PIN Safety Validation

## Scenario 1: Duress PIN Entered
- **Action**: User enters duress PIN (e.g., 9999)
- **Expected**: Silent SOS triggers, contacts notified discreetly
- **Validation**: Verify no visible alert, backend receives duress flag

## Scenario 2: Normal PIN Entered
- **Action**: User enters normal PIN (e.g., 1234)
- **Expected**: Normal check-in completes, no alerts
- **Validation**: Verify UI shows success, no hidden alerts

## Scenario 3: Coercion Detection
- **Action**: System detects coercion pattern
- **Expected**: Confidence score drops, monitoring activated
- **Validation**: Verify score changes, escalation triggers
