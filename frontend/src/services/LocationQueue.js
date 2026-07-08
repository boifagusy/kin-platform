import { IndexedDBAdapter } from './IndexedDBAdapter.js';

/**
 * LocationQueue — FIFO queue with IndexedDB persistence
 * 
 * Features:
 *   - Dependency injection for storage adapter
 *   - Configurable store key
 *   - FIFO ordering (oldest first)
 *   - Persistent across restarts
 * 
 * Methods:
 *   - enqueue(item)    - Add item to end of queue
 *   - dequeue()        - Remove and return first item
 *   - peek()           - Return first item without removing
 *   - size()           - Return number of items
 *   - clear()          - Remove all items
 *   - getAll()         - Return all items (oldest first)
 *   - isEmpty()        - Return true if queue is empty
 *   - hasItems()       - Alias for !isEmpty()
 * 
 * @param {StorageAdapter} adapter - Storage adapter instance (default: IndexedDBAdapter)
 * @param {string} key - Storage key for queue data (default: 'queue')
 */
export class LocationQueue {
  constructor(adapter = null, key = 'queue') {
    this.adapter = adapter || new IndexedDBAdapter();
    this.key = key;
    this._cache = null;
  }

  /**
   * Load queue data from storage
   * @private
   * @returns {Promise<any[]>}
   */
  async _load() {
    if (this._cache !== null) return this._cache;
    const data = await this.adapter.get(this.key);
    this._cache = data || [];
    return this._cache;
  }

  /**
   * Save queue data to storage
   * @private
   * @returns {Promise<void>}
   */
  async _save() {
    await this.adapter.set(this.key, this._cache);
  }

  /**
   * Add item to end of queue
   * @param {any} item - Item to enqueue
   * @returns {Promise<void>}
   */
  async enqueue(item) {
    await this._load();
    this._cache.push(item);
    await this._save();
  }

  /**
   * Remove and return first item (FIFO)
   * @returns {Promise<any>} The first item, or null if queue is empty
   */
  async dequeue() {
    await this._load();
    if (this._cache.length === 0) return null;
    const item = this._cache.shift();
    await this._save();
    return item;
  }

  /**
   * Return first item without removing
   * @returns {Promise<any>} The first item, or null if queue is empty
   */
  async peek() {
    await this._load();
    return this._cache.length > 0 ? this._cache[0] : null;
  }

  /**
   * Return number of items in queue
   * @returns {Promise<number>}
   */
  async size() {
    await this._load();
    return this._cache.length;
  }

  /**
   * Remove all items from queue
   * @returns {Promise<void>}
   */
  async clear() {
    this._cache = [];
    await this._save();
  }

  /**
   * Return all items (oldest first)
   * @returns {Promise<any[]>}
   */
  async getAll() {
    await this._load();
    return [...this._cache];
  }

  /**
   * Check if queue is empty
   * @returns {Promise<boolean>}
   */
  async isEmpty() {
    await this._load();
    return this._cache.length === 0;
  }

  /**
   * Check if queue has items
   * @returns {Promise<boolean>}
   */
  async hasItems() {
    return !(await this.isEmpty());
  }

  /**
   * Invalidate cache (forces reload from storage)
   * @returns {Promise<void>}
   */
  async invalidate() {
    this._cache = null;
  }

  /**
   * Retry a failed item
   * @param {any} item - Item to retry
   * @param {Function} operation - Async operation to execute
   * @param {number} maxAttempts - Maximum retry attempts (default: 3)
   * @param {number} backoffMs - Initial backoff in ms (default: 1000)
   * @returns {Promise<{success: boolean, attempts: number}>}
   */
  async retry(item, operation, maxAttempts = 3, backoffMs = 1000) {
    let attempts = 0;
    let lastError = null;

    while (attempts < maxAttempts) {
      try {
        attempts++;
        await operation(item);
        // Success — remove from failed list
        this._failed = (this._failed || []).filter(f => f !== item);
        return { success: true, attempts };
      } catch (error) {
        lastError = error;
        if (attempts < maxAttempts) {
          // Exponential backoff: 1s, 2s, 4s, 8s...
          const delay = backoffMs * Math.pow(2, attempts - 1);
          await new Promise(resolve => setTimeout(resolve, delay));
        }
      }
    }

    // Failed after max attempts
    this._failed = this._failed || [];
    this._failed.push(item);
    throw lastError || new Error('Retry failed after max attempts');
  }

  /**
   * Retry all items in the queue
   * @param {Function} operation - Async operation to execute for each item
   * @param {number} maxAttempts - Maximum retry attempts per item
   * @param {number} backoffMs - Initial backoff in ms
   * @returns {Promise<{success: number, failed: number}>}
   */
  async retryAll(operation, maxAttempts = 3, backoffMs = 1000) {
    await this._load();
    let success = 0;
    let failed = 0;

    for (const item of this._cache) {
      try {
        await this.retry(item, operation, maxAttempts, backoffMs);
        success++;
      } catch {
        failed++;
      }
    }

    return { success, failed };
  }

  /**
   * Get all failed items
   * @returns {any[]}
   */
  getFailed() {
    return this._failed || [];
  }

  /**
   * Clear failed items
   */
  clearFailed() {
    this._failed = [];
  }

  /**
   * Check if there are failed items
   * @returns {boolean}
   */
  hasFailed() {
    return (this._failed || []).length > 0;
  }
}
