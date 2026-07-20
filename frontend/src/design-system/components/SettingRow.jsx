export default function SettingRow({ icon, label, desc, trailing = 'chevron', onPress, className = '' }) {
  const trailingContent = () => {
    if (trailing === 'chevron') {
      return <span className="material-symbols-outlined text-gray-300 text-lg">chevron_right</span>;
    }
    if (trailing === 'value') {
      return <span className="text-sm text-gray-500">{desc}</span>;
    }
    return trailing;
  };

  return (
    <button
      onClick={onPress}
      className={`w-full flex items-center gap-4 px-5 py-4 text-left hover:bg-gray-50 transition-colors border-b border-gray-50 last:border-0 ${className}`}
      style={{ minHeight: '48px' }}
    >
      {icon && <span className="flex-shrink-0">{icon}</span>}
      <div className="flex-1 min-w-0">
        <p className="text-sm font-medium text-gray-900">{label}</p>
        {desc && trailing !== 'value' && <p className="text-xs text-gray-400 truncate">{desc}</p>}
      </div>
      {trailingContent()}
    </button>
  );
}
