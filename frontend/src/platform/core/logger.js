/**
 * Platform Core Logger
 * 
 * Centralized logging for the Platform layer.
 * Respects environment configuration.
 */

import { config } from './config';

const LOG_LEVELS = {
  DEBUG: 'DEBUG',
  INFO: 'INFO',
  WARN: 'WARN',
  ERROR: 'ERROR',
};

const LOG_LEVEL_VALUES = {
  DEBUG: 0,
  INFO: 1,
  WARN: 2,
  ERROR: 3,
};

class Logger {
  constructor() {
    this.enabled = config.logging.enabled;
    this.level = config.logging.level;
  }

  debug(message, data = null) {
    this._log(LOG_LEVELS.DEBUG, message, data);
  }

  info(message, data = null) {
    this._log(LOG_LEVELS.INFO, message, data);
  }

  warn(message, data = null) {
    this._log(LOG_LEVELS.WARN, message, data);
  }

  error(message, data = null) {
    this._log(LOG_LEVELS.ERROR, message, data);
  }

  _log(level, message, data) {
    if (!this.enabled) return;
    if (LOG_LEVEL_VALUES[level] < LOG_LEVEL_VALUES[this.level]) return;

    const timestamp = new Date().toISOString();
    const prefix = `[${timestamp}] [${level}] [Platform]`;
    
    if (data) {
      console.log(`${prefix} ${message}`, data);
    } else {
      console.log(`${prefix} ${message}`);
    }
  }
}

export const logger = new Logger();
export default logger;
