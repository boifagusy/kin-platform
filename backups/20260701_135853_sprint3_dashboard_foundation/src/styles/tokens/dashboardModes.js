/**
 * KIN Dashboard Modes
 */

export const DashboardMode = {
  COMPACT: 'compact',
  STANDARD: 'standard',
  OPERATIONS: 'operations',
  EMERGENCY: 'emergency',
}

export const dashboardModeConfig = {
  [DashboardMode.COMPACT]: {
    columns: 4,
    gap: 8,
    padding: 8,
    fontSize: 'sm',
    showTrends: false,
    showSecondaryMetrics: false,
    density: 'low',
  },
  [DashboardMode.STANDARD]: {
    columns: 4,
    gap: 16,
    padding: 16,
    fontSize: 'base',
    showTrends: true,
    showSecondaryMetrics: true,
    density: 'medium',
  },
  [DashboardMode.OPERATIONS]: {
    columns: 6,
    gap: 16,
    padding: 16,
    fontSize: 'base',
    showTrends: true,
    showSecondaryMetrics: true,
    density: 'high',
  },
  [DashboardMode.EMERGENCY]: {
    columns: 3,
    gap: 24,
    padding: 24,
    fontSize: 'lg',
    showTrends: true,
    showSecondaryMetrics: true,
    density: 'critical',
    emphasizeFailures: true,
    autoRefresh: true,
  },
}

export const getDashboardModeConfig = (mode) => {
  return dashboardModeConfig[mode] || dashboardModeConfig[DashboardMode.STANDARD]
}

export default {
  DashboardMode,
  dashboardModeConfig,
  getDashboardModeConfig,
}
