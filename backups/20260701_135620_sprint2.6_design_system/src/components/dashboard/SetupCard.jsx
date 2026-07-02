import { FaCheckCircle, FaArrowRight } from "react-icons/fa";

function SetupCard({ tasks, onTaskClick }) {
  if (!tasks || tasks.length === 0) return null;
  
  return (
    <div style={{ background: "white", borderRadius: 24, padding: 20, marginBottom: 16, boxShadow: "0 1px 3px rgba(0,0,0,0.1)" }}>
      <div style={{ display: "flex", alignItems: "center", gap: 8, marginBottom: 12 }}>
        <FaCheckCircle style={{ fontSize: 18, color: "#D4A017" }} />
        <h3 style={{ fontWeight: "bold", color: "#1A5632", margin: 0 }}>Complete Setup</h3>
      </div>
      {tasks.map((task) => (
        <div
          key={task.id}
          onClick={() => onTaskClick && onTaskClick(task)}
          style={{ display: "flex", alignItems: "center", justifyContent: "space-between", padding: "8px 0", borderBottom: "1px solid #f3f4f6", cursor: onTaskClick ? "pointer" : "default" }}
        >
          <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
            <div style={{ width: 6, height: 6, borderRadius: "50%", background: "#D4A017" }} />
            <span style={{ fontSize: 13, color: "#374151" }}>{task.title}</span>
          </div>
          <FaArrowRight style={{ color: "#9ca3af", fontSize: 12 }} />
        </div>
      ))}
    </div>
  );
}

export default SetupCard;
