import { FaChevronRight } from "react-icons/fa";

function ActivityTimeline({ activities, totalCount, onViewAll }) {
  if (!activities || activities.length === 0) {
    return null;
  }

  const recentActivities = activities.slice(0, 3);

  return (
    <div className="bg-white rounded-2xl p-5 shadow-sm">
      <div className="flex justify-between items-center mb-4">
        <h3 className="font-bold text-[#1A5632] text-base">Recent Activity</h3>
        {totalCount > 3 && (
          <button 
            onClick={onViewAll} 
            className="text-xs text-[#D4A017] font-semibold flex items-center gap-1 hover:gap-2 transition-all"
          >
            View All <FaChevronRight size={10} />
          </button>
        )}
      </div>
      <div className="space-y-3">
        {recentActivities.map((activity) => (
          <div key={activity.id} className="flex items-center gap-3 py-2 border-b border-gray-100 last:border-0">
            <span className="text-xl">{activity.icon}</span>
            <div className="flex-1">
              <p className="text-sm text-gray-800">{activity.message}</p>
              <p className="text-xs text-gray-400 mt-0.5">{activity.time_ago}</p>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

export default ActivityTimeline;
