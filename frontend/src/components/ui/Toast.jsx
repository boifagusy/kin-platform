import { useState, useEffect, useCallback } from 'react';

const toasts = [];
let listeners = [];

export function toast(message, type = 'info') {
  const id = Date.now();
  toasts.push({ id, message, type });
  listeners.forEach(fn => fn([...toasts]));
  setTimeout(() => {
    const idx = toasts.findIndex(t => t.id === id);
    if (idx > -1) toasts.splice(idx, 1);
    listeners.forEach(fn => fn([...toasts]));
  }, 4000);
}

export default function ToastContainer() {
  const [items, setItems] = useState([]);

  useEffect(() => {
    listeners.push(setItems);
    return () => { listeners = listeners.filter(fn => fn !== setItems); };
  }, []);

  if (items.length === 0) return null;

  const colors = {
    success: 'bg-emerald-500',
    error: 'bg-red-500',
    warning: 'bg-amber-500',
    info: 'bg-blue-500',
  };

  return (
    <div className="fixed bottom-20 left-0 right-0 z-50 flex flex-col items-center gap-2 px-4">
      {items.map(t => (
        <div key={t.id} className={`${colors[t.type] || colors.info} text-white px-5 py-3 rounded-xl shadow-lg text-sm font-medium animate-slide-up max-w-sm w-full text-center`}>
          {t.message}
        </div>
      ))}
    </div>
  );
}
