import { motion, useReducedMotion } from 'framer-motion';

export function ModalBackdrop({ children, onClose }) {
  const prefersReduced = useReducedMotion();

  return (
    <motion.div
      className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center"
      initial={prefersReduced ? {} : { opacity: 0 }}
      animate={{ opacity: 1 }}
      exit={{ opacity: 0 }}
      onClick={onClose}
    >
      {children}
    </motion.div>
  );
}

export function ModalContent({ children, className = '' }) {
  const prefersReduced = useReducedMotion();

  return (
    <motion.div
      className={className}
      initial={prefersReduced ? {} : { opacity: 0, scale: 0.9 }}
      animate={{ opacity: 1, scale: 1 }}
      exit={{ opacity: 0, scale: 0.9 }}
      transition={{ type: 'spring', stiffness: 300, damping: 30 }}
      onClick={(e) => e.stopPropagation()}
    >
      {children}
    </motion.div>
  );
}
