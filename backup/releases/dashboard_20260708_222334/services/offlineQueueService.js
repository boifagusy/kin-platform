export class OfflineQueueService {
  constructor() {
    this.storageKey = 'kin_offline_queue';
    this.initialize();
  }

  initialize() {
    if (!localStorage.getItem(this.storageKey)) {
      localStorage.setItem(this.storageKey, JSON.stringify([]));
    }
  }

  getQueue() {
    try {
      const data = localStorage.getItem(this.storageKey);
      return JSON.parse(data) || [];
    } catch (error) {
      return [];
    }
  }

  saveQueue(queue) {
    localStorage.setItem(this.storageKey, JSON.stringify(queue));
  }

  async queueCheckIn(data) {
    const queue = this.getQueue();
    const item = {
      id: `checkin_${Date.now()}`,
      type: 'checkin',
      priority: 'normal',
      data: data,
      status: 'pending',
      retries: 0,
      createdAt: new Date().toISOString()
    };
    queue.push(item);
    this.saveQueue(queue);
    return { queued: true, id: item.id, status: 'pending' };
  }

  async queueEmergency(data) {
    const queue = this.getQueue();
    const item = {
      id: `emergency_${Date.now()}`,
      type: 'emergency',
      priority: 'critical',
      data: data,
      status: 'pending',
      retries: 0,
      createdAt: new Date().toISOString()
    };
    queue.unshift(item); // Add to front for priority
    this.saveQueue(queue);
    return { queued: true, id: item.id, priority: 'critical' };
  }

  async markFailed(id) {
    const queue = this.getQueue();
    const item = queue.find(i => i.id === id);
    if (item) {
      item.status = 'failed';
      item.retries += 1;
      this.saveQueue(queue);
    }
    return true;
  }

  async retry(id) {
    const queue = this.getQueue();
    const item = queue.find(i => i.id === id);
    if (item) {
      item.status = 'pending';
      item.retries = 0;
      this.saveQueue(queue);
    }
    return { retried: true, status: 'pending' };
  }

  async syncAll() {
    const queue = this.getQueue();
    const pending = queue.filter(i => i.status === 'pending');
    const synced = pending.length;
    // In real implementation, this would send to API
    return { synced, failed: 0 };
  }

  async clear() {
    localStorage.removeItem(this.storageKey);
    this.initialize();
    return true;
  }
}

export const offlineQueueService = new OfflineQueueService();
export default offlineQueueService;

// Add these exports for backward compatibility
export const enqueue = async (data) => {
    const service = new OfflineQueueService();
    if (data.type === 'emergency') {
        return service.queueEmergency(data);
    }
    return service.queueCheckIn(data);
};

export const retryQueue = async () => {
    const service = new OfflineQueueService();
    const queue = service.getQueue();
    const failed = queue.filter(item => item.status === 'failed');
    for (const item of failed) {
        await service.retry(item.id);
    }
    return { retried: failed.length };
};
