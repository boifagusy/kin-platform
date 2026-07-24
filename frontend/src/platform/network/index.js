/**
 * Platform Network Module (Enhanced)
 * 
 * Exports HTTP client with retry, interceptors, normalization.
 * Centralizes all fetch() logic behind a single interface.
 */

export * as client from './client';
export * as headers from './headers';
export * as request from './request';
export * as response from './response';
export * as retry from './retry';
export * as interceptor from './interceptor';
export * as normalize from './normalize';

import * as networkClient from './client';

export default networkClient;
