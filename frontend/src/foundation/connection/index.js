// KIN Platform — Foundation Connection Layer
// Export all connection modules

export { default as ConnectionManager } from './ConnectionManager';
export { default as FetchClient } from './FetchClient';
export { default as EnvironmentManager } from './EnvironmentManager';
export { default as RetryManager } from './RetryManager';
export { default as HealthMonitor } from './HealthMonitor';
export { default as OfflineManager } from './OfflineManager';
export { default as DiagnosticsService } from './DiagnosticsService';

// Also export the singleton instance
export { default as environmentManager } from './EnvironmentManager';
