import { describe, it, expect, vi, beforeEach } from 'vitest';

// Import the actual class
import { OfflineQueueService } from '../../services/offlineQueueService';

describe('OfflineQueueService', () => {
  let queueService;

  beforeEach(() => {
    vi.clearAllMocks();
    localStorage.clear();
    queueService = new OfflineQueueService();
  });

  it('should queue check-in when offline', async () => {
    const checkinData = {
      pin: '1234',
      location: { lat: 6.5244, lng: 3.3792 },
      timestamp: new Date().toISOString()
    };

    const result = await queueService.queueCheckIn(checkinData);
    
    expect(result).toHaveProperty('queued', true);
    expect(result).toHaveProperty('id');
    expect(result).toHaveProperty('status', 'pending');
  });

  it('should queue emergency with priority', async () => {
    const emergencyData = {
      type: 'sos',
      location: { lat: 6.5244, lng: 3.3792 },
      isDuress: false
    };

    const result = await queueService.queueEmergency(emergencyData);
    
    expect(result).toHaveProperty('queued', true);
    expect(result).toHaveProperty('id');
    expect(result).toHaveProperty('priority', 'critical');
  });

  it('should get queue items', async () => {
    await queueService.queueCheckIn({ pin: '1234' });
    await queueService.queueEmergency({ type: 'sos' });

    const items = await queueService.getQueue();
    
    expect(items).toHaveLength(2);
    expect(items[0]).toHaveProperty('priority', 'critical');
  });

  it('should sync items when online', async () => {
    await queueService.queueCheckIn({ pin: '1234' });
    const syncResult = await queueService.syncAll();
    
    expect(syncResult).toHaveProperty('synced');
    expect(syncResult).toHaveProperty('failed');
    expect(typeof syncResult.synced).toBe('number');
  });

  it('should retry failed items', async () => {
    const item = await queueService.queueCheckIn({ pin: '1234' });
    await queueService.markFailed(item.id);
    const result = await queueService.retry(item.id);
    
    expect(result).toHaveProperty('retried', true);
    expect(result).toHaveProperty('status', 'pending');
  });

  it('should clear queue', async () => {
    await queueService.queueCheckIn({ pin: '1234' });
    const cleared = await queueService.clear();
    
    expect(cleared).toBe(true);
    const items = await queueService.getQueue();
    expect(items).toHaveLength(0);
  });
});
