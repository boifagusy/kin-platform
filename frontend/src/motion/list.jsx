import { motion, useReducedMotion } from 'framer-motion';
import { stagger, cardReveal } from './variants';

export default function ListMotion({ children, className = '' }) {
  const prefersReduced = useReducedMotion();

  return (
    <motion.div
      className={className}
      variants={prefersReduced ? {} : stagger(0.05)}
      initial="hidden"
      animate="visible"
    >
      {children}
    </motion.div>
  );
}

export function ListItemMotion({ children, className = '' }) {
  const prefersReduced = useReducedMotion();

  return (
    <motion.div
      className={className}
      variants={prefersReduced ? {} : cardReveal}
    >
      {children}
    </motion.div>
  );
}
