import React from 'react'
import { HealthStatus, getHealthConfig } from '../../../styles/tokens/healthStatus'
import { DashboardMode } from '../../../styles/tokens/dashboardModes'

const DashboardHeader = ({
  healthStatus,
  healthScore,
  onRefresh,
  mode,
  onModeChange,
  lastUpdated,
}) => {
  const statusConfig = getHealthConfig(healthStatus)
  const healthColor = statusConfig.color

  return (
    <div className="flex flex-wrap items-center justify-between gap-4 mb-6 bg-white dark:bg-neutral-900 p-4 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700">
      <div className="flex items-center gap-4">
        <div className="flex items-center gap-2">
          <div
            className="w-3 h-3 rounded-full animate-pulse"
            style={{ backgroundColor: healthColor }}
          />
          <h1 className="text-xl font-semibold text-neutral-900 dark:text-white">
            🔍 Watchtower
          </h1>
        </div>
        <div className="flex items-center gap-2">
          <span className="text-sm font-medium" style={{ color: healthColor }}>
            {statusConfig.badge}
          </span>
          {healthScore !== undefined && (
            <span className="text-sm text-neutral-500 dark:text-neutral-400">
              ({healthScore}%)
            </span>
          )}
        </div>
      </div>

      <div className="flex items-center gap-4">
        {/* Mode selector */}
        <div className="flex items-center gap-1 bg-neutral-100 dark:bg-neutral-800 rounded p-1">
          {Object.values(DashboardMode).map((m) => (
            <button
              key={m}
              onClick={() => onModeChange(m)}
              className={`px-3 py-1 text-xs rounded transition-colors ${
                mode === m
                  ? 'bg-white dark:bg-neutral-700 text-neutral-900 dark:text-white shadow-sm'
                  : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200'
              }`}
            >
              {m.charAt(0).toUpperCase() + m.slice(1)}
            </button>
          ))}
        </div>

        {/* Refresh button */}
        <button
          onClick={onRefresh}
          className="px-3 py-1 text-sm text-blue-500 hover:text-blue-600 transition-colors"
        >
          🔄 Refresh
        </button>

        {/* Last updated */}
        {lastUpdated && (
          <span className="text-xs text-neutral-400 dark:text-neutral-500">
            Updated: {new Date(lastUpdated).toLocaleTimeString()}
          </span>
        )}
      </div>
    </div>
  )
}

export default DashboardHeader
