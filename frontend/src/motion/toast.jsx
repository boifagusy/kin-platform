import { motion, useReducedMotion } from 'framer-motion';
import { slideDown } from './variants';

export default function ToastMotion({ children, className = '' }) {
  const prefersReduced = useReducedMotion();

  return (
    <motion.div
      className={className}
      initial={prefersReduced ? {} : { opacity: 0, y: -24 }}
      animate={prefersReduced ? {} : { opacity: 1, y: 0 }}
      exit={prefersReduced ? {} : { opacity: 0, y: -24 }}
      transition={{ duration: 0.22, ease: [0.0, 0.0, 0.2, 1.0] }}
    >
      {children}
    </motion.div>
  );
}
