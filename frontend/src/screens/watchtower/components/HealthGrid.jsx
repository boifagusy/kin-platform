import React from 'react'
import Card from '../../../components/ui/Card'
import { WidgetState } from '../../../components/ui/WidgetBase'

const HealthGrid = ({ data, mode }) => {
  const items = [
    {
      title: 'API',
      status: data.api?.status || 'unknown',
      primaryMetric: { value: data.api?.requests_per_minute || 0, label: 'req/min' },
      secondaryMetric: { label: 'Error rate', value: `${data.api?.error_rate || 0}%` },
    },
    {
      title: 'Queue',
      status: data.queue?.status || 'unknown',
      primaryMetric: { value: data.queue?.pending || 0, label: 'pending' },
      secondaryMetric: { label: 'Failed', value: data.queue?.failed || 0 },
    },
    {
      title: 'Database',
      status: data.database?.status || 'unknown',
      primaryMetric: { value: data.database?.connections || 0, label: 'connections' },
      secondaryMetric: { label: 'Tables', value: data.database?.tables || 0 },
    },
    {
      title: 'Storage',
      status: data.storage?.status || 'unknown',
      primaryMetric: { value: `${data.storage?.used_percent || 0}%`, label: 'used' },
      secondaryMetric: { label: 'Free', value: `${data.storage?.free_gb || 0} GB` },
    },
  ]

  const columns = mode === 'compact' ? 4 : mode === 'operations' ? 6 : 4

  return (
    <div
      className={`grid gap-${mode === 'compact' ? '2' : '4'} mb-6`}
      style={{ gridTemplateColumns: `repeat(${columns}, 1fr)` }}
    >
      {items.map((item, index) => (
        <Card
          key={index}
          title={item.title}
          status={item.status}
          primaryMetric={item.primaryMetric}
          secondaryMetric={item.secondaryMetric}
          state={item.status === 'unknown' ? WidgetState.ERROR : WidgetState.SUCCESS}
          className="h-full"
        />
      ))}
    </div>
  )
}

export default HealthGrid
