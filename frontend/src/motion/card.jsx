import { motion, useReducedMotion } from 'framer-motion';
import { cardReveal } from './variants';

export default function CardMotion({ children, className = '', index = 0 }) {
  const prefersReduced = useReducedMotion();

  return (
    <motion.div
      className={className}
      variants={prefersReduced ? {} : cardReveal}
      custom={index}
    >
      {children}
    </motion.div>
  );
}
