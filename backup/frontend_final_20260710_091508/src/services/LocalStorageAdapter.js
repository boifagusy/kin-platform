import { StorageAdapter } from './StorageAdapter.js';

/**
 * LocalStorageAdapter — localStorage implementation of StorageAdapter
 * 
 * Features:
 *   - Async interface (wraps sync localStorage)
 *   - JSON serialization/deserialization
 *   - Graceful handling of malformed JSON
 *   - Identical interface to IndexedDBAdapter
 */
export class LocalStorageAdapter extends StorageAdapter {
  /**
   * Retrieve a value by key
   * @param {string} key
   * @returns {Promise<any>}
   */
  async get(key) {
    try {
      const value = localStorage.getItem(key);
      if (value === null) return null;
      return JSON.parse(value);
    } catch (error) {
      console.warn(`[LocalStorageAdapter] Failed to parse key "${key}":`, error);
      return null;
    }
  }

  /**
   * Store a value by key
   * @param {string} key
   * @param {any} value
   * @returns {Promise<void>}
   */
  async set(key, value) {
    try {
      localStorage.setItem(key, JSON.stringify(value));
    } catch (error) {
      throw new Error(`[LocalStorageAdapter] Failed to set "${key}": ${error.message}`);
    }
  }

  /**
   * Delete a value by key
   * @param {string} key
   * @returns {Promise<void>}
   */
  async delete(key) {
    localStorage.removeItem(key);
  }

  /**
   * Clear all values
   * @returns {Promise<void>}
   */
  async clear() {
    localStorage.clear();
  }

  /**
   * Get all key-value pairs
   * @returns {Promise<Array<{key: string, value: any}>>}
   */
  async getAll() {
    const results = [];
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i);
      if (key) {
        try {
          const value = JSON.parse(localStorage.getItem(key));
          results.push({ key, value });
        } catch {
          // Skip malformed entries
        }
      }
    }
    return results;
  }

  /**
   * Get all keys
   * @returns {Promise<string[]>}
   */
  async getKeys() {
    const keys = [];
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i);
      if (key) keys.push(key);
    }
    return keys;
  }
}
