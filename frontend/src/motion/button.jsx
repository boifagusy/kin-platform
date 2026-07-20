import { motion, useReducedMotion } from 'framer-motion';
import { buttonTap } from './variants';

export default function ButtonMotion({ children, className = '', onClick, ...props }) {
  const prefersReduced = useReducedMotion();

  return (
    <motion.button
      className={className}
      whileTap={prefersReduced ? {} : buttonTap.tap}
      onClick={onClick}
      {...props}
    >
      {children}
    </motion.button>
  );
}
