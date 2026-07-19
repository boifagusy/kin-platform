import { duration, easing, distance, scale, opacity } from './tokens';

export const fadeIn = {
  hidden: { opacity: opacity.hidden },
  visible: { opacity: opacity.visible, transition: { duration: duration.normal, ease: easing.easeOut } },
};

export const slideUp = {
  hidden: { opacity: opacity.hidden, y: distance.md },
  visible: { opacity: opacity.visible, y: 0, transition: { duration: duration.normal, ease: easing.easeOut } },
};

export const slideDown = {
  hidden: { opacity: opacity.hidden, y: -distance.md },
  visible: { opacity: opacity.visible, y: 0, transition: { duration: duration.normal, ease: easing.easeOut } },
};

export const slideLeft = {
  hidden: { opacity: opacity.hidden, x: distance.md },
  visible: { opacity: opacity.visible, x: 0, transition: { duration: duration.slow, ease: easing.easeOut } },
};

export const slideRight = {
  hidden: { opacity: opacity.hidden, x: -distance.md },
  visible: { opacity: opacity.visible, x: 0, transition: { duration: duration.slow, ease: easing.easeOut } },
};

export const scaleIn = {
  hidden: { opacity: opacity.hidden, scale: 0.9 },
  visible: { opacity: opacity.visible, scale: scale.normal, transition: easing.spring },
};

export const cardReveal = {
  hidden: { opacity: opacity.hidden, scale: 0.95, y: distance.sm },
  visible: { opacity: opacity.visible, scale: scale.normal, y: 0, transition: { duration: duration.normal, ease: easing.easeOut } },
};

export const buttonTap = {
  tap: { scale: scale.pressed, transition: easing.spring },
};

export const stagger = (delay = 0.05) => ({
  visible: { transition: { staggerChildren: delay } },
});
