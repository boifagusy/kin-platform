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
 * @param {LocationQueue} queue - Queue instance with dequeue/enqueue/getAll/retry
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
   * Items that fail are retried via LocationQueue.retry() (max 3, exponential backoff).
   * Permanently failed items are moved to dead-letter queue.
   * FR-07: Failed items never block subsequent items.
   *
   * @param {number} limit - Max items to process this pass
   * @param {number} delayMs - Delay between items in ms
   * @returns {Promise<{synced: number, failed: number, dead: number}>}
   */
  async drain(limit = 10, delayMs = 0) {
    let synced = 0;
    let failed = 0;
    let dead = 0;

    for (let i = 0; i < limit; i++) {
      const item = await this.queue.dequeue();
      if (item === null) break;

      try {
        await this.syncFn(item);
        synced++;
      } catch (error) {
        // S1: Use canonical retry with max 3 attempts + exponential backoff
        try {
          await this.queue.retry(item, this.syncFn, 3, 1000);
          synced++;
        } catch (retryError) {
          // Exhausted retries — move to dead-letter queue
          await this.queue.addDeadLetter(item, {
            retry_count: 3,
            failure_reason: retryError.message || 'Unknown',
            failed_at: new Date().toISOString(),
            last_attempt_at: new Date().toISOString(),
          });
          dead++;
        }
      }

      // FR-07: Continue processing remaining items regardless of failures
      if (delayMs > 0 && i < limit - 1) {
        await new Promise(resolve => setTimeout(resolve, delayMs));
      }
    }

    return { synced, failed, dead };
  }

  /**
   * Run a sync pass immediately (typically called on service start).
   * @param {number} limit
   * @param {number} delayMs
   * @returns {Promise<{synced: number, failed: number, dead: number}>}
   */
  async syncOnStart(limit = 10, delayMs = 3000) {
    return this.drain(limit, delayMs);
  }

  /**
   * Register reconnect handler and run a sync pass.
   * @param {number} limit
   * @param {number} delayMs
   * @returns {Function} cleanup function
   */
  syncOnReconnect(limit = 10, delayMs = 2000) {
    if (typeof window !== 'undefined') {
      this._reconnectHandler = () => {
        this.drain(limit, delayMs).catch(err => {
          console.warn('SyncQueue: reconnect drain failed:', err);
        });
      };
      window.addEventListener('online', this._reconnectHandler);
    }

    return () => {
      if (this._reconnectHandler && typeof window !== 'undefined') {
        window.removeEventListener('online', this._reconnectHandler);
        this._reconnectHandler = null;
      }
    };
  }
}

export default SyncQueue;
