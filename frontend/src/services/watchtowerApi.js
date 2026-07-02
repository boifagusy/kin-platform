/**
 * Watchtower API Client
 * Fetches dashboard data from the aggregation endpoint
 */

const API_BASE = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000'

const fetcher = async (url) => {
  const response = await fetch(`${API_BASE}/api/watchtower${url}`, {
    headers: {
      'Content-Type': 'application/json',
    },
  })

  if (!response.ok) {
    const error = await response.json().catch(() => ({}))
    throw new Error(error.message || 'Failed to fetch dashboard data')
  }

  const data = await response.json()
  return data.data
}

// Simple SWR hook for dashboard data
export function useWatchtowerDashboard(refreshInterval = 30000) {
  const [data, setData] = useState(null)
  const [error, setError] = useState(null)
  const [isLoading, setIsLoading] = useState(true)

  const fetchData = async () => {
    try {
      setIsLoading(true)
      const result = await fetcher('/dashboard')
      setData(result)
      setError(null)
    } catch (err) {
      setError(err)
    } finally {
      setIsLoading(false)
    }
  }

  useEffect(() => {
    fetchData()
    const interval = setInterval(fetchData, refreshInterval)
    return () => clearInterval(interval)
  }, [refreshInterval])

  return { data, error, isLoading, refresh: fetchData }
}
