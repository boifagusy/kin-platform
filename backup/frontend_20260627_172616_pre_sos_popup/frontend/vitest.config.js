import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: ['./src/test/setup.js'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'html', 'lcov', 'json'],
      exclude: [
        'node_modules/',
        'src/test/',
        '**/*.d.ts',
        '**/*.config.*',
        '**/index.js',
        '**/main.jsx',
        'src/App.jsx',
        'src/router.jsx',
        'src/utils/constants.js',
      ],
      thresholds: {
        global: {
          branches: 70,
          functions: 70,
          lines: 70,
          statements: 70,
        },
        './src/services/': {
          branches: 80,
          functions: 80,
          lines: 80,
          statements: 80,
        },
        './src/screens/ui-polish/': {
          branches: 65,
          functions: 65,
          lines: 65,
          statements: 65,
        },
      },
    },
    include: ['src/**/*.{test,spec}.{js,jsx,ts,tsx}'],
    exclude: ['node_modules', 'dist', 'build'],
    watch: false,
    testTimeout: 10000,
    hookTimeout: 10000,
    // Add these to handle issues
    isolate: false,
    passWithNoTests: true,
  },
});
