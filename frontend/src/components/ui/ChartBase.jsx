/**
 * KIN Chart Base Component
 * Recharts wrapper with standardized behavior
 */

import React from 'react'
import {
  LineChart,
  AreaChart,
  BarChart,
  PieChart,
  Line,
  Area,
  Bar,
  Pie,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
} from 'recharts'
import { WidgetBase, WidgetState } from './WidgetBase'

export const ChartTypes = {
  LINE: 'line',
  AREA: 'area',
  BAR: 'bar',
  PIE: 'pie',
  GAUGE: 'gauge',
}

export const ChartBase = ({
  type = ChartTypes.LINE,
  data = [],
  xKey = 'name',
  yKey = 'value',
  colors = ['#0066CC'],
  state = WidgetState.SUCCESS,
  height = 300,
  loadingComponent,
  emptyComponent,
  errorComponent,
  ...props
}) => {
  if (state === WidgetState.LOADING) {
    return loadingComponent || (
      <div className="flex items-center justify-center h-[300px] bg-neutral-50 dark:bg-neutral-800 rounded-lg animate-pulse">
        <div className="text-neutral-400">Loading chart...</div>
      </div>
    )
  }

  if (state === WidgetState.EMPTY) {
    return emptyComponent || (
      <div className="flex items-center justify-center h-[300px] bg-neutral-50 dark:bg-neutral-800 rounded-lg">
        <div className="text-neutral-400 text-sm">No data available</div>
      </div>
    )
  }

  if (state === WidgetState.ERROR) {
    return errorComponent || (
      <div className="flex items-center justify-center h-[300px] bg-neutral-50 dark:bg-neutral-800 rounded-lg">
        <div className="text-red-500 text-sm">Error loading chart</div>
      </div>
    )
  }

  if (state === WidgetState.OFFLINE) {
    return (
      <div className="flex items-center justify-center h-[300px] bg-neutral-50 dark:bg-neutral-800 rounded-lg">
        <div className="text-neutral-400 text-sm">Offline - please refresh</div>
      </div>
    )
  }

  const renderChart = () => {
    switch (type) {
      case ChartTypes.LINE:
        return (
          <LineChart data={data}>
            <CartesianGrid strokeDasharray="3 3" stroke="#e8e8e8" />
            <XAxis dataKey={xKey} stroke="#737373" fontSize={12} />
            <YAxis stroke="#737373" fontSize={12} />
            <Tooltip />
            <Legend />
            {colors.map((color, index) => (
              <Line
                key={index}
                type="monotone"
                dataKey={yKey}
                stroke={color}
                strokeWidth={2}
                dot={{ fill: color }}
              />
            ))}
          </LineChart>
        )
      case ChartTypes.AREA:
        return (
          <AreaChart data={data}>
            <CartesianGrid strokeDasharray="3 3" stroke="#e8e8e8" />
            <XAxis dataKey={xKey} stroke="#737373" fontSize={12} />
            <YAxis stroke="#737373" fontSize={12} />
            <Tooltip />
            <Legend />
            {colors.map((color, index) => (
              <Area
                key={index}
                type="monotone"
                dataKey={yKey}
                stroke={color}
                fill={color}
                fillOpacity={0.2}
              />
            ))}
          </AreaChart>
        )
      case ChartTypes.BAR:
        return (
          <BarChart data={data}>
            <CartesianGrid strokeDasharray="3 3" stroke="#e8e8e8" />
            <XAxis dataKey={xKey} stroke="#737373" fontSize={12} />
            <YAxis stroke="#737373" fontSize={12} />
            <Tooltip />
            <Legend />
            {colors.map((color, index) => (
              <Bar key={index} dataKey={yKey} fill={color} radius={[4, 4, 0, 0]} />
            ))}
          </BarChart>
        )
      case ChartTypes.PIE:
        return (
          <PieChart>
            <Pie
              data={data}
              dataKey={yKey}
              nameKey={xKey}
              cx="50%"
              cy="50%"
              outerRadius={100}
              fill="#0066CC"
              label
            />
            <Tooltip />
            <Legend />
          </PieChart>
        )
      default:
        return <div className="text-center py-8 text-neutral-400">Unsupported chart type</div>
    }
  }

  return (
    <div className="chart-container w-full">
      <ResponsiveContainer width="100%" height={height}>
        {renderChart()}
      </ResponsiveContainer>
    </div>
  )
}

export default ChartBase
