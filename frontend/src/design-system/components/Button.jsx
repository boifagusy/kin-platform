import ButtonMotion from '../../motion/button';

const variants = {
  primary: 'bg-[#1A5632] text-white',
  secondary: 'bg-gray-100 text-gray-700',
  danger: 'bg-[#DC2626] text-white',
  ghost: 'bg-transparent text-gray-600',
};

const sizes = {
  sm: 'px-3 py-1.5 text-xs',
  md: 'px-5 py-2.5 text-sm',
  lg: 'px-6 py-3 text-base',
};

export default function Button({ children, variant = 'primary', size = 'md', onClick, disabled, className = '' }) {
  return (
    <ButtonMotion
      onClick={onClick}
      disabled={disabled}
      className={`rounded-xl font-semibold transition-colors ${variants[variant]} ${sizes[size]} ${disabled ? 'opacity-50' : ''} ${className}`}
    >
      {children}
    </ButtonMotion>
  );
}
