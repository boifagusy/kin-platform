import React from 'react'
import Card from '../../../components/ui/Card'
import { WidgetState } from '../../../components/ui/WidgetBase'
import { HealthStatus } from '../../../styles/tokens/healthStatus'

const ExecutiveCards = ({ data, mode }) => {
  const health = data.health || { status: 'unknown', score: 0 }
  const incidents = data.incidents || { active: 0, critical: 0, warning: 0 }
  const performance = data.performance || { response_time: 0 }

  const cards = [
    {
      title: 'Health Score',
      status: health.status,
      primaryMetric: {
        value: `${health.score || 0}%`,
        label: 'Overall System Health',
      },
      trend: health.score > 75
        ? { direction: 'up', value: 5, label: 'vs last week' }
        : { direction: 'down', value: 2, label: 'vs last week' },
    },
    {
      title: 'Active Incidents',
      status: incidents.active > 0 ? 'warning' : 'healthy',
      primaryMetric: {
        value: incidents.active || 0,
        label: 'Critical: ' + (incidents.critical || 0),
      },
      trend: incidents.active > 0
        ? { direction: 'up', value: 12, label: 'vs last week' }
        : { direction: 'down', value: 8, label: 'vs last week' },
    },
    {
      title: 'Response Time',
      status: performance.response_time < 100 ? 'excellent' : 'healthy',
      primaryMetric: {
        value: `${performance.response_time || 0}ms`,
        label: 'Average API Response',
      },
      trend: { direction: 'up', value: 3, label: 'vs last week' },
    },
    {
      title: 'Uptime',
      status: 'excellent',
      primaryMetric: {
        value: '99.9%',
        label: 'System Availability',
      },
      trend: { direction: 'up', value: 0.1, label: 'vs last week' },
    },
  ]

  return (
    <div
      className={`grid gap-${mode === 'compact' ? '2' : '4'} mb-6`}
      style={{
        gridTemplateColumns: `repeat(${mode === 'compact' ? 4 : mode === 'operations' ? 6 : 4}, 1fr)`,
      }}
    >
      {cards.map((card, index) => (
        <Card
          key={index}
          title={card.title}
          status={card.status}
          primaryMetric={card.primaryMetric}
          trend={card.trend}
          state={WidgetState.SUCCESS}
          className="h-full"
        />
      ))}
    </div>
  )
}

export default ExecutiveCards
