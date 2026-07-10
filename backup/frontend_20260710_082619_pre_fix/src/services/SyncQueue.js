/**
 * SyncQueue — wraps a queue (e.g. LocationQueue) and syncs its items
 * to a remote endpoint via a provided async sync function.
 *
 * Usage:
 *   const syncQueue = new SyncQueue(new LocationQueue(), async (item) => {
 *     await api.postLocation(item);
 *     return true;
 *   });
 *
 * @param {LocationQueue} queue - Queue instance with dequeue/enqueue/getAll
 * @param {Function} syncFn - async (item) => boolean|void. Throw to signal failure.
 */
export class SyncQueue {
  constructor(queue, syncFn) {
    this.queue = queue;
    this.syncFn = syncFn;
    this._reconnectHandler = null;
  }

  /**
   * Drain the queue, syncing each item in FIFO order.
   * Items that fail are re-enqueued at the end (not lost).
   * @param {number} limit - Max items to process this pass
   * @param {number} delayMs - Delay between items in ms
   * @returns {Promise<{synced: number, failed: number}>}
   */
  async drain(limit = 10, delayMs = 0) {
    let synced = 0;
    let failed = 0;

    for (let i = 0; i < limit; i++) {
      const item = await this.queue.dequeue();
      if (item === null) break;

      try {
        await this.syncFn(item);
        synced++;
      } catch (error) {
        console.warn('SyncQueue: item failed to sync, re-enqueueing', error);
        await this.queue.enqueue(item);
        failed++;
      }

      if (delayMs > 0 && i < limit - 1) {
        await new Promise(resolve => setTimeout(resolve, delayMs));
      }
    }

    return { synced, failed };
  }

  /**
   * Run a sync pass immediately (typically called on service start).
   * @param {number} limit
   * @param {number} delayMs
   * @returns {Promise<{synced: number, failed: number}>}
   */
  async syncOnStart(limit = 10, delayMs = 3000) {
    console.log('\ud83d\udd04 SyncQueue: syncing on start');
    return this.drain(limit, delayMs);
  }

  /**
   * Attach a listener that triggers a sync pass whenever the browser
   * regains connectivity (online event).
   * @param {number} limit
   * @param {number} delayMs
   * @returns {Function} cleanup function to remove the listener
   */
  syncOnReconnect(limit = 10, delayMs = 2000) {
    this._reconnectHandler = () => {
      console.log('\ud83d\udd04 SyncQueue: reconnected, syncing');
      this.drain(limit, delayMs).catch(err => {
        console.warn('SyncQueue: reconnect sync failed', err);
      });
    };

    window.addEventListener('online', this._reconnectHandler);

    return () => {
      if (this._reconnectHandler) {
        window.removeEventListener('online', this._reconnectHandler);
        this._reconnectHandler = null;
      }
    };
  }
}
