import '@testing-library/jest-dom/vitest';
import { vi } from 'vitest';

// Mock localStorage
const localStorageMock = {
  getItem: vi.fn(),
  setItem: vi.fn(),
  removeItem: vi.fn(),
  clear: vi.fn(),
};
global.localStorage = localStorageMock;

// Mock Capacitor
global.Capacitor = {
  isNativePlatform: false,
  registerPlugin: vi.fn(),
};

// Mock navigator
Object.defineProperty(window, 'navigator', {
  value: {
    geolocation: {
      getCurrentPosition: vi.fn(),
      watchPosition: vi.fn(),
    },
  },
  writable: true,
});

// Mock window functions if needed
window.alert = vi.fn();
window.confirm = vi.fn();

// Mock React Router
vi.mock('react-router-dom', async () => {
  const actual = await vi.importActual('react-router-dom');
  return {
    ...actual,
    useNavigate: () => vi.fn(),
    useLocation: () => ({ pathname: '/' }),
  };
});

// Mock Capacitor plugins
vi.mock('@capacitor/core', () => ({
  Capacitor: {
    isNativePlatform: false,
  },
  Plugins: {
    Geolocation: {
      getCurrentPosition: vi.fn(),
      watchPosition: vi.fn(),
    },
    LocalNotifications: {
      schedule: vi.fn(),
      requestPermissions: vi.fn(),
    },
  },
}));

console.log('✅ Test setup complete');
