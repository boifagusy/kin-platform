# KIN Component Inventory

## UI Components (Reusable)

### Core Components
- `Button` - Primary, secondary, ghost, danger variants
- `Card` - Standardized card with header, body, footer
- `WidgetBase` - Base widget with lifecycle states
- `SkeletonLoader` - Loading placeholder
- `LoadingScreen` - Full page loading

### Form Components
- `Input` - Text, email, password, number
- `Select` - Dropdown selection
- `Checkbox` - Toggle checkbox
- `Radio` - Radio button group
- `Switch` - Toggle switch

### Layout Components
- `Grid` - Responsive grid system
- `Container` - Max-width container
- `Stack` - Vertical/horizontal stacking

### Data Display
- `Card` - Metric card
- `ChartBase` - Chart wrapper (Recharts)
- `Table` - Data table with sorting
- `Badge` - Status indicator
- `Tag` - Small label

### Feedback Components
- `Toast` - Notification toast
- `Modal` - Dialog modal
- `Alert` - Alert banner
- `Progress` - Progress bar
- `Spinner` - Loading spinner

### Navigation Components
- `Tabs` - Tab navigation
- `Breadcrumb` - Breadcrumb trail
- `Pagination` - Page navigation

### Health Components
- `HealthBadge` - Status badge
- `HealthCard` - Status card
- `HealthIndicator` - Status indicator

## Watchtower Components

### Dashboard
- `ExecutiveCards` - KPI cards
- `HealthGrid` - System health grid
- `PluginGrid` - Plugin status grid
- `IncidentTimeline` - Incident timeline
- `DeploymentHistory` - Deployment list

### Charts
- `LineChart` - Line chart
- `AreaChart` - Area chart
- `BarChart` - Bar chart
- `PieChart` - Pie chart
- `GaugeChart` - Gauge chart

## Rules
1. **Reuse before create** - Check this inventory before creating new components
2. **Extend before refactor** - Extend existing components when possible
3. **Document new components** - Add to this inventory
4. **Design tokens first** - Use design tokens, never hardcode
5. **Accessibility required** - All components must be accessible
