import { useState, useEffect } from 'react';

const API_BASE = import.meta.env.VITE_API_URL || '/api/v1';

export default function NotificationInbox({ onClose }) {
  const [notifications, setNotifications] = useState([]);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState('all');

  const fetchNotifications = async () => {
    const token = localStorage.getItem('kin_token');
    const res = await fetch(`${API_BASE}/notifications`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    const data = await res.json();
    setNotifications(data.data || []);
    setLoading(false);
  };

  const markAllRead = async () => {
    const token = localStorage.getItem('kin_token');
    await fetch(`${API_BASE}/notifications/read-all`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token}` },
    });
    fetchNotifications();
  };

  useEffect(() => { fetchNotifications(); }, []);

  const categories = ['all', 'security', 'marketing', 'system'];
  const filtered = filter === 'all' ? notifications : notifications.filter(n => n.category === filter);

  const icons = { security: '🔴', marketing: '📢', system: 'ℹ️', incident: '🚨', campaign: '📤', announcement: '📋' };

  if (loading) return <div className="p-4 text-center text-sm text-gray-500">Loading...</div>;

  return (
    <div className="fixed inset-0 bg-black/50 z-50 flex justify-end">
      <div className="w-full max-w-sm bg-white h-full overflow-y-auto shadow-xl">
        <div className="sticky top-0 bg-white border-b px-4 py-3 flex items-center justify-between">
          <h2 className="font-bold text-lg">Notifications</h2>
          <div className="flex gap-2">
            <button onClick={markAllRead} className="text-xs text-blue-600">Mark All Read</button>
            <button onClick={onClose} className="text-lg">✕</button>
          </div>
        </div>
        <div className="flex gap-1 px-4 py-2 overflow-x-auto border-b">
          {categories.map(c => (
            <button key={c} onClick={() => setFilter(c)}
              className={`px-3 py-1 rounded-full text-xs whitespace-nowrap ${filter === c ? 'bg-[#1A5632] text-white' : 'bg-gray-100 text-gray-600'}`}>
              {c.charAt(0).toUpperCase() + c.slice(1)}
            </button>
          ))}
        </div>
        <div className="divide-y">
          {filtered.length === 0 ? (
            <p className="p-4 text-center text-sm text-gray-400">No notifications</p>
          ) : (
            filtered.map(n => (
              <div key={n.id} className={`px-4 py-3 flex gap-3 ${!n.read ? 'bg-blue-50' : ''}`}>
                <span className="text-xl flex-shrink-0 mt-0.5">{icons[n.type] || '📌'}</span>
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-medium text-gray-800">{n.title}</p>
                  <p className="text-xs text-gray-500 truncate">{n.message}</p>
                  <p className="text-[10px] text-gray-400 mt-1">{new Date(n.created_at).toLocaleString()}</p>
                </div>
                {!n.read && <div className="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0 mt-1" />}
              </div>
            ))
          )}
        </div>
      </div>
    </div>
  );
}
