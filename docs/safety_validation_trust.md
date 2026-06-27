# Device Trust Safety Validation

## Scenario 1: Trusted Device
- **Expected**: Trust score ≥ 80, full functionality
- **Validation**: Verify fingerprint matches, score range

## Scenario 2: New Device
- **Expected**: Trust score 50-79, additional verification
- **Validation**: Verify score range, verification flow

## Scenario 3: Suspicious Device
- **Expected**: Trust score < 50, monitoring activated
- **Validation**: Verify score range, monitoring started

## Scenario 4: Rooted Device
- **Expected**: Trust score < 30, limited functionality
- **Validation**: Verify score range, restrictions applied
