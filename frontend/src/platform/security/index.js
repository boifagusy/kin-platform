/**
 * Platform Security Module
 * 
 * Exports token management and authentication utilities.
 */

export * as token from './token';
export * as auth from './auth';

export default {
  token: require('./token'),
  auth: require('./auth'),
};
