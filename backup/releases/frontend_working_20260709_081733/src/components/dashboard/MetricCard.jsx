function MetricCard({ icon, label, value }) {
  return (
    <div className="bg-white rounded-2xl p-4 shadow-sm">
      <div className="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mb-3">
        {icon}
      </div>
      <p className="text-xs text-gray-500">{label}</p>
      <p className="text-xl font-bold text-[#1A5632] mt-1">{value}</p>
    </div>
  );
}

export default MetricCard;
