/**
 * KIN Theme System
 * Supports: Light, Dark, High Contrast, Emergency
 */

export const Theme = {
  LIGHT: 'light',
  DARK: 'dark',
  HIGH_CONTRAST: 'high-contrast',
  EMERGENCY: 'emergency',
}

export const themes = {
  [Theme.LIGHT]: {
    background: '#FFFFFF',
    surface: '#FAFAFA',
    text: '#171717',
    textSecondary: '#525252',
    border: '#E8E8E8',
    shadow: 'rgba(0,0,0,0.05)',
    card: '#FFFFFF',
  },
  [Theme.DARK]: {
    background: '#0A0A0A',
    surface: '#171717',
    text: '#FAFAFA',
    textSecondary: '#A3A3A3',
    border: '#404040',
    shadow: 'rgba(0,0,0,0.3)',
    card: '#262626',
  },
  [Theme.HIGH_CONTRAST]: {
    background: '#000000',
    surface: '#000000',
    text: '#FFFFFF',
    textSecondary: '#FFFFFF',
    border: '#FFFFFF',
    shadow: 'none',
    card: '#000000',
  },
  [Theme.EMERGENCY]: {
    background: '#1A0000',
    surface: '#2A0000',
    text: '#FF4444',
    textSecondary: '#FF8888',
    border: '#FF0000',
    shadow: 'rgba(255,0,0,0.2)',
    card: '#330000',
  },
}

export const getTheme = (theme) => {
  return themes[theme] || themes[Theme.LIGHT]
}

export default {
  Theme,
  themes,
  getTheme,
}
