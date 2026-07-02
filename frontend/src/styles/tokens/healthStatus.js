/**
 * KIN Health Status System
 * Standardized across all KIN modules
 */

export const HealthStatus = {
  EXCELLENT: 'excellent',
  HEALTHY: 'healthy',
  DEGRADED: 'degraded',
  WARNING: 'warning',
  CRITICAL: 'critical',
  OFFLINE: 'offline',
  MAINTENANCE: 'maintenance',
  UNKNOWN: 'unknown',
}

export const healthStatusConfig = {
  [HealthStatus.EXCELLENT]: {
    color: '#00C853',
    icon: 'check-circle',
    badge: 'Excellent',
    label: 'All systems operating perfectly',
    animation: 'pulse',
    priority: 1,
  },
  [HealthStatus.HEALTHY]: {
    color: '#4CAF50',
    icon: 'check-circle',
    badge: 'Healthy',
    label: 'Systems operating normally',
    animation: 'none',
    priority: 2,
  },
  [HealthStatus.DEGRADED]: {
    color: '#FFC107',
    icon: 'alert-triangle',
    badge: 'Degraded',
    label: 'Some minor issues detected',
    animation: 'pulse-slow',
    priority: 3,
  },
  [HealthStatus.WARNING]: {
    color: '#FF6F00',
    icon: 'alert-triangle',
    badge: 'Warning',
    label: 'Action recommended',
    animation: 'pulse',
    priority: 4,
  },
  [HealthStatus.CRITICAL]: {
    color: '#D32F2F',
    icon: 'alert-circle',
    badge: 'Critical',
    label: 'Immediate action required',
    animation: 'pulse-fast',
    priority: 5,
  },
  [HealthStatus.OFFLINE]: {
    color: '#757575',
    icon: 'power-off',
    badge: 'Offline',
    label: 'Not responding',
    animation: 'none',
    priority: 6,
  },
  [HealthStatus.MAINTENANCE]: {
    color: '#FF9800',
    icon: 'tools',
    badge: 'Maintenance',
    label: 'Planned downtime',
    animation: 'none',
    priority: 7,
  },
  [HealthStatus.UNKNOWN]: {
    color: '#9E9E9E',
    icon: 'help-circle',
    badge: 'Unknown',
    label: 'Status unknown',
    animation: 'none',
    priority: 8,
  },
}

export const getHealthConfig = (status) => {
  return healthStatusConfig[status] || healthStatusConfig[HealthStatus.UNKNOWN]
}

export const getHealthColor = (status) => {
  return getHealthConfig(status).color
}

export const getHealthIcon = (status) => {
  return getHealthConfig(status).icon
}

export const getHealthBadge = (status) => {
  return getHealthConfig(status).badge
}

export const getHealthLabel = (status) => {
  return getHealthConfig(status).label
}

export default {
  HealthStatus,
  healthStatusConfig,
  getHealthConfig,
  getHealthColor,
  getHealthIcon,
  getHealthBadge,
  getHealthLabel,
}
