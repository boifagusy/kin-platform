function FeaturePill({ icon, text }) {
  return (
    <div className="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-border bg-surface shadow-sm">
      <span className="material-symbols-outlined text-primary text-base">
        {icon}
      </span>
      <span className="text-sm font-medium text-text-primary">{text}</span>
    </div>
  );
}

export default FeaturePill;
