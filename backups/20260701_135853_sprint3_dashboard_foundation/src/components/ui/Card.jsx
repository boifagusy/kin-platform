/**
 * KIN Card Component
 * Standardized card structure for all dashboards
 */

import React from 'react'
import { WidgetBase, WidgetState } from './WidgetBase'
import { getHealthConfig } from '../../styles/tokens/healthStatus'

export const Card = ({
  title,
  status,
  primaryMetric,
  trend,
  secondaryMetric,
  lastUpdated,
  actions,
  state = WidgetState.SUCCESS,
  className = '',
  children,
  ...props
}) => {
  const statusConfig = status ? getHealthConfig(status) : null

  return (
    <WidgetBase
      state={state}
      title={title}
      status={status}
      className={`card ${className}`}
      {...props}
    >
      <div className="card-content">
        {/* Primary Metric */}
        {primaryMetric && (
          <div className="card-primary-metric">
            <div className="text-2xl font-semibold text-neutral-900 dark:text-white">
              {primaryMetric.value}
            </div>
            {primaryMetric.label && (
              <div className="text-sm text-neutral-500 dark:text-neutral-400">
                {primaryMetric.label}
              </div>
            )}
          </div>
        )}

        {/* Trend */}
        {trend && (
          <div className="card-trend mt-2 flex items-center gap-1 text-sm">
            <span className={trend.direction === 'up' ? 'text-green-500' : 'text-red-500'}>
              {trend.direction === 'up' ? '↑' : '↓'}
            </span>
            <span>{trend.value}%</span>
            <span className="text-neutral-400">{trend.label}</span>
          </div>
        )}

        {/* Secondary Metric */}
        {secondaryMetric && (
          <div className="card-secondary-metric mt-1 text-sm text-neutral-500 dark:text-neutral-400">
            {secondaryMetric.label}: {secondaryMetric.value}
          </div>
        )}

        {/* Children */}
        {children && (
          <div className="card-children mt-3">
            {children}
          </div>
        )}

        {/* Last Updated */}
        {lastUpdated && (
          <div className="card-last-updated mt-3 text-xs text-neutral-400 dark:text-neutral-500">
            Updated: {lastUpdated}
          </div>
        )}

        {/* Actions */}
        {actions && (
          <div className="card-actions mt-3 flex gap-2">
            {actions.map((action, index) => (
              <button
                key={index}
                onClick={action.onClick}
                className="text-sm text-blue-500 hover:underline"
              >
                {action.label}
              </button>
            ))}
          </div>
        )}
      </div>
    </WidgetBase>
  )
}

export default Card
