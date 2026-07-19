import PageMotion from '../../motion/page';

export default function ScreenLayout({ children, className = '' }) {
  return (
    <PageMotion className={`min-h-screen bg-[#F0F7F2] ${className}`}>
      {children}
    </PageMotion>
  );
}
