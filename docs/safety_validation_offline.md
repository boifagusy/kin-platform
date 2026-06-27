# Offline Mode Safety Validation

## Scenario 1: Check-in While Offline
- **Action**: User performs check-in with no internet
- **Expected**: Check-in queued locally, shows "pending sync"
- **Validation**: Verify queue has item, status is "pending"

## Scenario 2: Emergency While Offline
- **Action**: User triggers SOS with no internet
- **Expected**: Emergency queued with priority "critical"
- **Validation**: Verify queue priority, item at front

## Scenario 3: Network Restored
- **Action**: Internet connection restored
- **Expected**: All queued items synced, priority maintained
- **Validation**: Verify queue emptied, items sent to API

## Scenario 4: Duress While Offline
- **Action**: User enters duress PIN while offline
- **Expected**: Queued with duress flag, silent sync
- **Validation**: Verify duress flag, no UI indication
