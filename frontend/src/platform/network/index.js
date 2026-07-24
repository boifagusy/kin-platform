/**
 * Platform Network Module
 * 
 * Exports HTTP client and utilities.
 * Centralizes all fetch() logic behind a single interface.
 */

export * as client from './client';
export * as headers from './headers';
export * as request from './request';
export * as response from './response';

import * as networkClient from './client';

export default networkClient;
