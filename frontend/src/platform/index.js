/**
 * KIN Platform
 * 
 * Foundation layer providing:
 * - Core configuration, constants, logging, errors
 * - Network client with consistent headers and error handling
 * - Security and token management
 * 
 * This layer is invisible to the application.
 * All public APIs remain unchanged.
 */

export * as core from './core';
export * as network from './network';
export * as security from './security';

export default {
  core: require('./core'),
  network: require('./network'),
  security: require('./security'),
};
