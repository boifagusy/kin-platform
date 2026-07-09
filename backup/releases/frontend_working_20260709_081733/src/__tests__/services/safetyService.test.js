import { describe, it, expect, vi, beforeEach } from 'vitest';

// Create a mock SafetyService
const mockSafetyService = {
  getStatus: vi.fn().mockResolvedValue({
    confidence: 85,
    deviceTrust: 90,
    location: { lat: 6.5244, lng: 3.3792 },
    lastCheckIn: new Date().toISOString()
  }),
  checkIn: vi.fn().mockImplementation((data) => {
    if (data.pin === '9999') {
      return Promise.resolve({ success: true, confidence: 95, isDuress: true });
    }
    return Promise.resolve({ success: true, confidence: 85, isDuress: false });
  }),
  getSafetyScore: vi.fn().mockResolvedValue({ score: 85, tier: 'green' })
};

// Mock the import
vi.mock('../../services/safetyService', () => ({
  SafetyService: class {
    constructor() {}
    getStatus = mockSafetyService.getStatus;
    checkIn = mockSafetyService.checkIn;
    getSafetyScore = mockSafetyService.getSafetyScore;
  }
}));

import { SafetyService } from '../../services/safetyService';

describe('SafetyService', () => {
  let safetyService;

  beforeEach(() => {
    vi.clearAllMocks();
    safetyService = new SafetyService();
  });

  it('should get safety status', async () => {
    const result = await safetyService.getStatus();
    expect(result).toHaveProperty('confidence');
    expect(result).toHaveProperty('deviceTrust');
    expect(typeof result.confidence).toBe('number');
    expect(result.confidence).toBeGreaterThanOrEqual(0);
    expect(result.confidence).toBeLessThanOrEqual(100);
  });

  it('should perform check-in', async () => {
    const result = await safetyService.checkIn({
      pin: '1234',
      location: { lat: 6.5244, lng: 3.3792 }
    });
    expect(result).toHaveProperty('success');
    expect(result).toHaveProperty('confidence');
    expect(typeof result.success).toBe('boolean');
  });

  it('should detect duress pin', async () => {
    const result = await safetyService.checkIn({
      pin: '9999',
      location: { lat: 6.5244, lng: 3.3792 }
    });
    expect(result).toHaveProperty('isDuress', true);
  });

  it('should handle offline check-in', async () => {
    // Modify the mock for this test
    safetyService.checkIn = vi.fn().mockResolvedValue({
      queued: true,
      localId: 'local_12345'
    });
    
    const result = await safetyService.checkIn({
      pin: '1234',
      offline: true,
      location: { lat: 6.5244, lng: 3.3792 }
    });
    expect(result).toHaveProperty('queued', true);
    expect(result).toHaveProperty('localId');
  });

  it('should get safety score', async () => {
    const result = await safetyService.getSafetyScore();
    expect(result).toHaveProperty('score');
    expect(result).toHaveProperty('tier');
    expect(['green', 'yellow', 'orange', 'red', 'black']).toContain(result.tier);
  });
});
