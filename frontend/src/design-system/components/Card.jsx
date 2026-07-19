import CardMotion from '../../motion/card';

const paddings = { sm: 'p-3', md: 'p-4', lg: 'p-5' };

export default function Card({ children, padding = 'md', className = '', index = 0 }) {
  return (
    <CardMotion className={`bg-white rounded-2xl shadow-sm ${paddings[padding]} ${className}`} index={index}>
      {children}
    </CardMotion>
  );
}
