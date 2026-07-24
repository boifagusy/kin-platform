/**
 * Platform Core Module
 * 
 * Exports core utilities, configuration, and error handling.
 */

export { config, default as defaultConfig } from './config';
export * as constants from './constants';
export * as errors from './errors';
export { logger, default as defaultLogger } from './logger';

export default {
  config,
  constants: require('./constants'),
  errors: require('./errors'),
  logger,
};
