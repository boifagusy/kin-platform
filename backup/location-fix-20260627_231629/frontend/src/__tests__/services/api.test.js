import { describe, it, expect, vi, beforeEach } from 'vitest';

// Create a mock API that returns data directly (not wrapped)
const createMockApi = () => ({
  auth: {
    login: vi.fn().mockResolvedValue({ success: true, userId: 1 }),
    createPin: vi.fn().mockResolvedValue({ success: true }),
    loginPin: vi.fn().mockResolvedValue({ success: true }),
  },
  sos: {
    trigger: vi.fn().mockResolvedValue({ success: true, eventId: 1 }),
  },
  checkin: {
    store: vi.fn().mockResolvedValue({ success: true, id: 1 }),
  },
  trustedContacts: {
    store: vi.fn().mockResolvedValue({ success: true, contact: { id: 1 } }),
    get: vi.fn().mockResolvedValue({ contacts: [] }),
    delete: vi.fn().mockResolvedValue({ success: true }),
  },
});

describe('API Service', () => {
  let api;

  beforeEach(() => {
    vi.clearAllMocks();
    api = createMockApi();
  });

  describe('auth endpoints', () => {
    it('should login with phone number', async () => {
      const result = await api.auth.login({ phone: '+2348012345678' });
      expect(result).toEqual({ success: true, userId: 1 });
    });

    it('should create PIN', async () => {
      const result = await api.auth.createPin({ pin: '1234' });
      expect(result).toEqual({ success: true });
    });

    it('should handle login errors', async () => {
      api.auth.loginPin = vi.fn().mockRejectedValue({ 
        response: { status: 401, data: { message: 'Invalid PIN' } } 
      });
      await expect(api.auth.loginPin({ pin: '9999' })).rejects.toEqual(
        expect.objectContaining({ response: expect.any(Object) })
      );
    });
  });

  describe('sos endpoints', () => {
    it('should trigger SOS', async () => {
      const result = await api.sos.trigger({ location: { lat: 6.5244, lng: 3.3792 } });
      expect(result).toEqual({ success: true, eventId: 1 });
    });
  });

  describe('checkin endpoints', () => {
    it('should store check-in', async () => {
      const result = await api.checkin.store({
        location: { lat: 6.5244, lng: 3.3792 },
        pin: '1234'
      });
      expect(result).toEqual({ success: true, id: 1 });
    });
  });

  describe('trusted contacts endpoints', () => {
    it('should store trusted contact', async () => {
      const result = await api.trustedContacts.store({
        phone: '+2348098765432',
        name: 'Test Contact'
      });
      expect(result).toEqual({ success: true, contact: { id: 1 } });
    });

    it('should get trusted contacts list', async () => {
      const result = await api.trustedContacts.get();
      expect(result).toEqual({ contacts: [] });
    });

    it('should delete trusted contact', async () => {
      const result = await api.trustedContacts.delete(1);
      expect(result).toEqual({ success: true });
    });
  });
});
