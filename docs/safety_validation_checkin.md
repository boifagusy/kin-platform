# Check-in Safety Validation

## Scenario 1: Normal Check-in
- **Action**: User performs check-in on time
- **Expected**: Confidence score ≥ 80 (Green)
- **Validation**: API returns success, score updated

## Scenario 2: 4-Hour Missed Check-in
- **Action**: User misses check-in by 4 hours
- **Expected**: Confidence score 60-79 (Yellow)
- **Validation**: System sends gentle reminder

## Scenario 3: 8-Hour Missed Check-in
- **Action**: User misses check-in by 8 hours
- **Expected**: Confidence score 40-59 (Orange)
- **Validation**: System alerts emergency contacts

## Scenario 4: 24-Hour Missed Check-in
- **Action**: User misses check-in by 24 hours
- **Expected**: Confidence score 20-39 (Red)
- **Validation**: System escalates to authorities

## Scenario 5: 48-Hour Missed Check-in
- **Action**: User misses check-in by 48 hours
- **Expected**: Confidence score 0-19 (Black)
- **Validation**: Full emergency protocol activated
