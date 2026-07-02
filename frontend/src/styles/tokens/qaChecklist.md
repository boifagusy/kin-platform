# KIN QA Acceptance Checklist

## Component Verification

### Design Tokens
- [ ] No hardcoded colors
- [ ] No hardcoded spacing
- [ ] Uses design tokens
- [ ] Theme-aware

### Accessibility
- [ ] Keyboard navigation works
- [ ] Visible focus states
- [ ] ARIA labels present
- [ ] Screen reader compatible
- [ ] Touch target ≥ 44px

### Responsive
- [ ] Works on phone
- [ ] Works on tablet
- [ ] Works on desktop
- [ ] Works on ultra-wide

### Widget States
- [ ] Loading state
- [ ] Success state
- [ ] Warning state
- [ ] Critical state
- [ ] Offline state
- [ ] Maintenance state
- [ ] Error state
- [ ] Empty state

### Animations
- [ ] Uses approved durations (150ms, 250ms, 400ms)
- [ ] Uses approved easing
- [ ] No random animations

### Documentation
- [ ] Component documented
- [ ] Props documented
- [ ] Usage examples
- [ ] Added to inventory

### Testing
- [ ] Unit tests pass
- [ ] Integration tests pass
- [ ] Visual regression pass
- [ ] Performance tests pass

## Design System Compliance
- [ ] Matches KIN design language
- [ ] Reuses existing components when possible
- [ ] Extends rather than duplicates
- [ ] Documented why reuse wasn't possible (if new)

## Browser Compatibility
- [ ] Chrome
- [ ] Firefox
- [ ] Safari
- [ ] Edge
- [ ] Mobile Safari (iOS)
- [ ] Mobile Chrome (Android)
