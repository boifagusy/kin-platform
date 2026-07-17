const API_BASE = import.meta.env.VITE_API_URL || '/api/v1';
const CACHE_KEY = 'kin_announcements';
const DISMISSED_KEY = 'kin_dismissed_announcements';

class AnnouncementService {
  async fetch() {
    try {
      const res = await fetch(`${API_BASE}/announcements?platform=android&version=0.0.0`);
      const data = await res.json();
      if (data.success) {
        localStorage.setItem(CACHE_KEY, JSON.stringify(data.data));
        return this.filterDismissed(data.data);
      }
    } catch (e) {
      const cached = localStorage.getItem(CACHE_KEY);
      if (cached) return this.filterDismissed(JSON.parse(cached));
    }
    return [];
  }

  filterDismissed(announcements) {
    const dismissed = JSON.parse(localStorage.getItem(DISMISSED_KEY) || '[]');
    return announcements.filter(a => !dismissed.includes(a.id));
  }

  dismiss(id) {
    const dismissed = JSON.parse(localStorage.getItem(DISMISSED_KEY) || '[]');
    if (!dismissed.includes(id)) {
      dismissed.push(id);
      localStorage.setItem(DISMISSED_KEY, JSON.stringify(dismissed));
    }
  }

  getDismissed() {
    return JSON.parse(localStorage.getItem(DISMISSED_KEY) || '[]');
  }
}

export default new AnnouncementService();
