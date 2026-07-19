import { motion, useReducedMotion } from 'framer-motion';
import { slideLeft, fadeIn } from './variants';

export default function PageMotion({ children, className = '' }) {
  const prefersReduced = useReducedMotion();

  return (
    <motion.div
      className={className}
      variants={prefersReduced ? {} : slideLeft}
      initial="hidden"
      animate="visible"
    >
      {children}
    </motion.div>
  );
}
