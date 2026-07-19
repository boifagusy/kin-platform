export default function SectionHeader({ title, className = '' }) {
  return (
    <h3 className={`text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-1 ${className}`}>
      {title}
    </h3>
  );
}
