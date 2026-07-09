function SafetyScoreCardMinimal({ score, label }) {
  const getColor = () => {
    if (score >= 70) return "text-green-600";
    if (score >= 50) return "text-yellow-600";
    return "text-red-600";
  };

  return (
    <div className="bg-white rounded-2xl px-4 py-3 shadow-sm border border-[#E9ECEF]">
      <div className="flex items-center justify-between">
        <div>
          <p className="text-xs font-medium text-[#6C757D]">Safety Score</p>
          <p className={`text-sm font-bold ${getColor()}`}>{score}% · {label}</p>
        </div>
        <div className="text-2xl font-bold text-[#1A5632]">{score}%</div>
      </div>
    </div>
  );
}

export default SafetyScoreCardMinimal;
