import { FaChevronRight } from "react-icons/fa";

function ActivityTimeline({ activities, totalCount, onViewAll }) {
  if (!activities || activities.length === 0) {
    return null;
  }

  // Helper function to format activity data
  const formatActivity = (activity) => {
    // If it's already formatted (has icon, message, time_ago), return as is
    if (activity.icon && activity.message) {
      return activity;
    }

    // ✅ If the activity is a string, try to parse it as JSON
    let parsedActivity = activity;
    if (typeof activity === 'string') {
      try {
        parsedActivity = JSON.parse(activity);
      } catch (e) {
        // If it's not valid JSON, treat it as a plain message
        return {
          id: Date.now(),
          icon: "📋",
          message: activity,
          time_ago: "",
        };
      }
    }

    // Now transform the parsed data
    let icon = "📋";
    let message = "Activity recorded";
    let timeAgo = "";

    // Check for SOS activity
    if (parsedActivity.sos_id) {
      icon = "🚨";
      message = `SOS Alert triggered (ID: ${parsedActivity.sos_id})`;
    }
    // Check for check-in activity
    else if (parsedActivity.check_in_id) {
      icon = "✅";
      message = `Check-in completed (ID: ${parsedActivity.check_in_id})`;
    }
    // Check for incident
    else if (parsedActivity.incident_id) {
      icon = "📋";
      const incidentType = parsedActivity.type || "Safety";
      message = `${incidentType} incident (ID: ${parsedActivity.incident_id})`;
    }
    // Check for escalation
    else if (parsedActivity.escalation_id) {
      icon = "⬆️";
      message = `Escalation triggered (ID: ${parsedActivity.escalation_id})`;
    }
    // Check for contacts notified
    else if (parsedActivity.contacts_notified !== undefined) {
      icon = "📱";
      message = `${parsedActivity.contacts_notified} contact(s) notified`;
    }
    // Check for event type
    else if (parsedActivity.type || parsedActivity.event_type) {
      icon = "📌";
      message = parsedActivity.type || parsedActivity.event_type;
    }

    // Calculate time ago
    const timestamp = parsedActivity.created_at || parsedActivity.timestamp || parsedActivity.occurred_at || parsedActivity.triggered_at;
    if (timestamp) {
      timeAgo = getTimeAgo(timestamp);
    }

    return {
      id: parsedActivity.id || parsedActivity.sos_id || parsedActivity.incident_id || Date.now(),
      icon,
      message,
      time_ago: timeAgo,
    };
  };

  // Helper to calculate time ago
  const getTimeAgo = (timestamp) => {
    const now = new Date();
    const past = new Date(timestamp);
    const diffMs = now - past;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return "Just now";
    if (diffMins < 60) return `${diffMins} min ago`;
    if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
  };

  // Transform all activities
  const formattedActivities = activities.map(formatActivity);
  const recentActivities = formattedActivities.slice(0, 3);

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
          <div key={activity.id || Date.now() + Math.random()} className="flex items-center gap-3 py-2 border-b border-gray-100 last:border-0">
            <span className="text-xl">{activity.icon}</span>
            <div className="flex-1">
              <p className="text-sm text-gray-800">{activity.message}</p>
              {activity.time_ago && (
                <p className="text-xs text-gray-400 mt-0.5">{activity.time_ago}</p>
              )}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

export default ActivityTimeline;
