/**
 * KIN Design System Tokens
 * Single source of truth for all KIN interfaces
 */

export const colors = {
  // Brand Colors
  brand: {
    primary: '#0066CC',
    secondary: '#6C5CE7',
    accent: '#00C853',
    warning: '#FFC107',
    danger: '#D32F2F',
    info: '#2196F3',
    success: '#4CAF50',
  },
  // Semantic Colors
  semantic: {
    excellent: '#00C853',
    healthy: '#4CAF50',
    degraded: '#FFC107',
    warning: '#FF6F00',
    critical: '#D32F2F',
    offline: '#757575',
    maintenance: '#FF9800',
    unknown: '#9E9E9E',
  },
  // Neutral Palette
  neutral: {
    50: '#FAFAFA',
    100: '#F5F5F5',
    200: '#E8E8E8',
    300: '#D4D4D4',
    400: '#A3A3A3',
    500: '#737373',
    600: '#525252',
    700: '#404040',
    800: '#262626',
    900: '#171717',
    950: '#0A0A0A',
  },
  // Status Colors
  status: {
    success: '#4CAF50',
    warning: '#FFC107',
    error: '#D32F2F',
    info: '#2196F3',
  },
}

export const spacing = {
  xs: 4,
  sm: 8,
  md: 12,
  lg: 16,
  xl: 24,
  xxl: 32,
  xxxl: 48,
  xxxxl: 64,
  gap: {
    xs: 4,
    sm: 8,
    md: 12,
    lg: 16,
    xl: 24,
  },
  padding: {
    xs: 4,
    sm: 8,
    md: 12,
    lg: 16,
    xl: 24,
    xxl: 32,
  },
}

export const typography = {
  fontFamily: {
    sans: 'ui-sans-serif, system-ui, -apple-system, sans-serif',
    mono: 'ui-monospace, SFMono-Regular, monospace',
  },
  fontSize: {
    xs: 12,
    sm: 14,
    base: 16,
    lg: 18,
    xl: 20,
    '2xl': 24,
    '3xl': 30,
    '4xl': 36,
  },
  fontWeight: {
    normal: 400,
    medium: 500,
    semibold: 600,
    bold: 700,
  },
  lineHeight: {
    tight: 1.25,
    normal: 1.5,
    relaxed: 1.75,
  },
}

export const radius = {
  none: 0,
  sm: 4,
  md: 8,
  lg: 12,
  xl: 16,
  xxl: 24,
  full: 9999,
}

export const elevation = {
  sm: '0 1px 2px rgba(0,0,0,0.05)',
  md: '0 4px 6px rgba(0,0,0,0.07)',
  lg: '0 10px 15px rgba(0,0,0,0.1)',
  xl: '0 20px 25px rgba(0,0,0,0.15)',
  xxl: '0 40px 50px rgba(0,0,0,0.2)',
}

export const borders = {
  width: {
    thin: 1,
    medium: 2,
    thick: 4,
  },
  style: {
    solid: 'solid',
    dashed: 'dashed',
    dotted: 'dotted',
  },
}

export const opacity = {
  disabled: 0.4,
  hover: 0.8,
  active: 1.0,
  overlay: 0.5,
}

export const animation = {
  duration: {
    instant: 0,
    fast: 150,
    normal: 250,
    slow: 400,
  },
  easing: {
    ease: 'ease',
    easeIn: 'ease-in',
    easeOut: 'ease-out',
    easeInOut: 'ease-in-out',
    linear: 'linear',
  },
}

export const breakpoints = {
  phone: 0,
  tablet: 768,
  desktop: 1024,
  wide: 1440,
  ultra: 1920,
}

export const zIndex = {
  base: 1,
  dropdown: 100,
  sticky: 200,
  modal: 1000,
  tooltip: 2000,
  toast: 3000,
  overlay: 4000,
}

export const touchTarget = {
  min: 44,
}

export const designTokens = {
  colors,
  spacing,
  typography,
  radius,
  elevation,
  borders,
  opacity,
  animation,
  breakpoints,
  zIndex,
  touchTarget,
}

export default designTokens
