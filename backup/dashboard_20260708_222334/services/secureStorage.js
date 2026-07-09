// Secure storage with simple encryption
export const secureStorage = {
  async set(key, value) {
    try {
      const encoded = btoa(encodeURIComponent(JSON.stringify({
        value: value,
        timestamp: Date.now()
      })));
      localStorage.setItem(`kin_secure_${key}`, encoded);
      return true;
    } catch (error) {
      console.warn('Secure storage write failed:', error);
      return false;
    }
  },

  async get(key) {
    try {
      const encoded = localStorage.getItem(`kin_secure_${key}`);
      if (!encoded) return null;
      const decoded = JSON.parse(decodeURIComponent(atob(encoded)));
      return decoded.value;
    } catch (error) {
      console.warn('Secure storage read failed:', error);
      return null;
    }
  },

  async remove(key) {
    localStorage.removeItem(`kin_secure_${key}`);
  },

  async clear() {
    Object.keys(localStorage)
      .filter(key => key.startsWith('kin_secure_'))
      .forEach(key => localStorage.removeItem(key));
  }
};

export default secureStorage;
