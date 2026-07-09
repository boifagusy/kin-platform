import { StorageAdapter } from './StorageAdapter.js';

/**
 * IndexedDBAdapter — IndexedDB implementation of StorageAdapter
 * 
 * Defaults:
 *   Database: KinLocationDB
 *   Store: locationQueue
 * 
 * Features:
 *   - Lazy database initialization
 *   - Automatic object store creation
 *   - Transaction error handling
 *   - Promise-based API only
 */
export class IndexedDBAdapter extends StorageAdapter {
  constructor(dbName = 'KinLocationDB', storeName = 'locationQueue') {
    super();
    this.dbName = dbName;
    this.storeName = storeName;
    this.db = null;
    this.initialized = false;
  }

  /**
   * Initialize the database connection
   * @private
   * @returns {Promise<void>}
   */
  async _init() {
    if (this.initialized && this.db) return;

    return new Promise((resolve, reject) => {
      const request = indexedDB.open(this.dbName, 1);

      request.onupgradeneeded = (event) => {
        const db = event.target.result;
        if (!db.objectStoreNames.contains(this.storeName)) {
          db.createObjectStore(this.storeName, { keyPath: 'key' });
        }
      };

      request.onsuccess = (event) => {
        this.db = event.target.result;
        this.initialized = true;
        resolve();
      };

      request.onerror = (event) => {
        reject(new Error(`IndexedDB open failed: ${event.target.error}`));
      };
    });
  }

  /**
   * Get a transaction for the object store
   * @private
   * @param {string} mode - 'readonly' or 'readwrite'
   * @returns {Promise<IDBObjectStore>}
   */
  async _getStore(mode) {
    await this._init();
    const transaction = this.db.transaction([this.storeName], mode);
    return transaction.objectStore(this.storeName);
  }

  /**
   * Retrieve a value by key
   * @param {string} key
   * @returns {Promise<any>}
   */
  async get(key) {
    const store = await this._getStore('readonly');
    return new Promise((resolve, reject) => {
      const request = store.get(key);
      request.onsuccess = () => resolve(request.result?.value ?? null);
      request.onerror = () => reject(new Error(`IndexedDB get failed: ${request.error}`));
    });
  }

  /**
   * Store a value by key
   * @param {string} key
   * @param {any} value
   * @returns {Promise<void>}
   */
  async set(key, value) {
    const store = await this._getStore('readwrite');
    return new Promise((resolve, reject) => {
      const request = store.put({ key, value });
      request.onsuccess = () => resolve();
      request.onerror = () => reject(new Error(`IndexedDB set failed: ${request.error}`));
    });
  }

  /**
   * Delete a value by key
   * @param {string} key
   * @returns {Promise<void>}
   */
  async delete(key) {
    const store = await this._getStore('readwrite');
    return new Promise((resolve, reject) => {
      const request = store.delete(key);
      request.onsuccess = () => resolve();
      request.onerror = () => reject(new Error(`IndexedDB delete failed: ${request.error}`));
    });
  }

  /**
   * Clear all values
   * @returns {Promise<void>}
   */
  async clear() {
    const store = await this._getStore('readwrite');
    return new Promise((resolve, reject) => {
      const request = store.clear();
      request.onsuccess = () => resolve();
      request.onerror = () => reject(new Error(`IndexedDB clear failed: ${request.error}`));
    });
  }

  /**
   * Get all key-value pairs
   * @returns {Promise<Array<{key: string, value: any}>>}
   */
  async getAll() {
    const store = await this._getStore('readonly');
    return new Promise((resolve, reject) => {
      const request = store.getAll();
      request.onsuccess = () => {
        const results = request.result || [];
        resolve(results.map(item => ({ key: item.key, value: item.value })));
      };
      request.onerror = () => reject(new Error(`IndexedDB getAll failed: ${request.error}`));
    });
  }

  /**
   * Get all keys
   * @returns {Promise<string[]>}
   */
  async getKeys() {
    const store = await this._getStore('readonly');
    return new Promise((resolve, reject) => {
      const request = store.getAllKeys();
      request.onsuccess = () => resolve(request.result || []);
      request.onerror = () => reject(new Error(`IndexedDB getKeys failed: ${request.error}`));
    });
  }
}
