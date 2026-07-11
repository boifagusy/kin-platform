import { api } from './api';

export class SafetyService {
  constructor() {
    this.baseUrl = '/api/v1/safety';
  }

  async getStatus() {
    try {
      const response = await api.get(`${this.baseUrl}/status`);
      return response.data;
    } catch (error) {
      console.error('Error getting safety status:', error);
      return { confidence: 50, deviceTrust: 50, location: null };
    }
  }

  async checkIn(data) {
    try {
      const response = await api.post('/api/v1/checkin', data);
      return response.data;
    } catch (error) {
      if (error.code === 'ERR_NETWORK' || data.offline) {
        // Queue for offline
        return { queued: true, localId: `local_${Date.now()}` };
      }
      throw error;
    }
  }

  async getSafetyScore() {
    try {
      const response = await api.get(`${this.baseUrl}/score`);
      return response.data;
    } catch (error) {
      return { score: 50, tier: 'yellow' };
    }
  }
}

export const safetyService = new SafetyService();
export default safetyService;
