/**
 * KIN Widget Base Component
 * Every widget must support exactly the same lifecycle
 */

import React from 'react'
import { SkeletonLoader } from './SkeletonLoader'
import { healthStatusConfig } from '../../styles/tokens/healthStatus'

export const WidgetState = {
  LOADING: 'loading',
  SUCCESS: 'success',
  WARNING: 'warning',
  CRITICAL: 'critical',
  OFFLINE: 'offline',
  MAINTENANCE: 'maintenance',
  ERROR: 'error',
  EMPTY: 'empty',
}

export const WidgetBase = ({
  state = WidgetState.SUCCESS,
  title,
  status,
  children,
  loadingComponent,
  skeletonProps = {},
  errorComponent,
  emptyComponent,
  offlineComponent,
  className = '',
  ...props
}) => {
  // Loading State
  if (state === WidgetState.LOADING) {
    return loadingComponent || <SkeletonLoader {...skeletonProps} />
  }

  // Error State
  if (state === WidgetState.ERROR) {
    return errorComponent || (
      <div className="p-4 text-center">
        <div className="text-red-500 text-sm">Unable to load data</div>
        <button className="mt-2 text-sm text-blue-500 hover:underline">Retry</button>
      </div>
    )
  }

  // Empty State
  if (state === WidgetState.EMPTY) {
    return emptyComponent || (
      <div className="p-4 text-center text-neutral-400 text-sm">
        No data available
      </div>
    )
  }

  // Offline State
  if (state === WidgetState.OFFLINE) {
    return offlineComponent || (
      <div className="p-4 text-center">
        <div className="text-neutral-400 text-sm">Offline</div>
        <button className="mt-2 text-sm text-blue-500 hover:underline">Refresh</button>
      </div>
    )
  }

  // Success State (with optional status)
  const statusColor = status ? healthStatusConfig[status]?.color : 'transparent'
  const statusLabel = status ? healthStatusConfig[status]?.badge : ''

  return (
    <div className={`bg-white dark:bg-neutral-900 rounded-lg shadow-sm p-4 ${className}`} {...props}>
      {title && (
        <div className="flex items-center justify-between mb-2">
          <h3 className="text-sm font-medium text-neutral-700 dark:text-neutral-300">{title}</h3>
          {status && (
            <span className="text-xs font-medium" style={{ color: statusColor }}>
              {statusLabel}
            </span>
          )}
        </div>
      )}
      {children}
    </div>
  )
}

export default WidgetBase
