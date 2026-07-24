/**
 * Platform Security Module (Enhanced)
 * 
 * Exports token management, authentication, session, and authorization utilities.
 */

export * as token from './token';
export * as auth from './auth';
export * as session from './session';
export * as authorization from './authorization';

export default {
  token: require('./token'),
  auth: require('./auth'),
  session: require('./session'),
  authorization: require('./authorization'),
};
