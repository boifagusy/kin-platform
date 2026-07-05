// KIN Foundation — Export all modules

// Config (Single Source of Truth)
export { default as ConfigService } from './config/ConfigService';
export { default as configService } from './config/ConfigService';

// Connection
export { default as ConnectionManager } from './connection/ConnectionManager';
export { default as FetchClient } from './connection/FetchClient';
export { default as RetryManager } from './connection/RetryManager';
export { default as HealthMonitor } from './connection/HealthMonitor';
export { default as OfflineManager } from './connection/OfflineManager';
export { default as DiagnosticsService } from './connection/DiagnosticsService';

// Events
export { default as EventBus } from './events/EventBus';

// Metrics
export { default as ConnectionMetrics } from './metrics/ConnectionMetrics';

// Context
export { ConnectivityProvider, useConnectivity } from './context/ConnectivityContext';

// Hooks
export { useConnectivity } from './hooks/useConnectivity';
