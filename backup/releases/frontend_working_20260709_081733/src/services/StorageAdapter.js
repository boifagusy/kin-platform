/**
 * StorageAdapter Interface
 * All storage adapters must implement these methods.
 * Provides a unified abstraction for different storage backends.
 */
export class StorageAdapter {
  /**
   * Retrieve a value by key
   * @param {string} key - The key to retrieve
   * @returns {Promise<any>} The stored value, or null if not found
   */
  async get(key) {
    throw new Error('Must implement get()');
  }

  /**
   * Store a value by key
   * @param {string} key - The key to store under
   * @param {any} value - The value to store
   * @returns {Promise<void>}
   */
  async set(key, value) {
    throw new Error('Must implement set()');
  }

  /**
   * Delete a value by key
   * @param {string} key - The key to delete
   * @returns {Promise<void>}
   */
  async delete(key) {
    throw new Error('Must implement delete()');
  }

  /**
   * Clear all values
   * @returns {Promise<void>}
   */
  async clear() {
    throw new Error('Must implement clear()');
  }

  /**
   * Get all key-value pairs
   * @returns {Promise<Array<{key: string, value: any}>>}
   */
  async getAll() {
    throw new Error('Must implement getAll()');
  }

  /**
   * Get all keys
   * @returns {Promise<string[]>}
   */
  async getKeys() {
    throw new Error('Must implement getKeys()');
  }
}
