/**
 * Platform Core Error Classes
 * 
 * Standardized error handling across the Platform layer.
 */

export class PlatformError extends Error {
  constructor(message, code = 'PLATFORM_ERROR') {
    super(message);
    this.name = 'PlatformError';
    this.code = code;
  }
}

export class NetworkError extends PlatformError {
  constructor(message, statusCode = null) {
    super(message, 'NETWORK_ERROR');
    this.name = 'NetworkError';
    this.statusCode = statusCode;
  }
}

export class AuthenticationError extends PlatformError {
  constructor(message = 'Authentication required') {
    super(message, 'AUTHENTICATION_ERROR');
    this.name = 'AuthenticationError';
  }
}

export class AuthorizationError extends PlatformError {
  constructor(message = 'Insufficient permissions') {
    super(message, 'AUTHORIZATION_ERROR');
    this.name = 'AuthorizationError';
  }
}

export class ValidationError extends PlatformError {
  constructor(message, details = {}) {
    super(message, 'VALIDATION_ERROR');
    this.name = 'ValidationError';
    this.details = details;
  }
}

export class TimeoutError extends PlatformError {
  constructor(message = 'Request timeout') {
    super(message, 'TIMEOUT_ERROR');
    this.name = 'TimeoutError';
  }
}

export default {
  PlatformError,
  NetworkError,
  AuthenticationError,
  AuthorizationError,
  ValidationError,
  TimeoutError,
};
