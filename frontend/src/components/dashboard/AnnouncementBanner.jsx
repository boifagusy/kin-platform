import { useState, useEffect, useRef, useCallback } from 'react';
import announcementService from '../../services/AnnouncementService.js';

function AnnouncementBanner() {
  const [announcements, setAnnouncements] = useState([]);
  const [currentIndex, setCurrentIndex] = useState(0);
  const [loading, setLoading] = useState(true);
  const mounted = useRef(true);
  const rotateTimer = useRef(null);
  const refreshTimer = useRef(null);

  const icons = { info: '📢', warning: '📢', success: '📢', critical: '📢' };
  const colors = {
    info:     'bg-blue-50 border-blue-200 text-blue-700',
    warning:  'bg-amber-50 border-amber-200 text-amber-700',
    success:  'bg-emerald-50 border-emerald-200 text-emerald-700',
    critical: 'bg-red-50 border-red-200 text-red-700',
  };

  const fetchAndShow = useCallback(async () => {
    try {
      const items = await announcementService.fetch();
      if (!mounted.current) return;
      setAnnouncements(items);
      setCurrentIndex(0);
    } catch (e) {
      // Silent — cached data shown by service
    } finally {
      if (mounted.current) setLoading(false);
    }
  }, []);

  useEffect(() => {
    mounted.current = true;
    fetchAndShow();
    return () => { mounted.current = false; };
  }, [fetchAndShow]);

  // Rotate announcements every 10 seconds
  useEffect(() => {
    if (announcements.length > 1) {
      rotateTimer.current = setInterval(() => {
        setCurrentIndex(prev => (prev + 1) % announcements.length);
      }, 10000);
    }
    return () => clearInterval(rotateTimer.current);
  }, [announcements.length]);

  // Refresh every 5 minutes
  useEffect(() => {
    refreshTimer.current = setInterval(fetchAndShow, 300000);
    return () => clearInterval(refreshTimer.current);
  }, [fetchAndShow]);

  const handleDismiss = async (id) => {
    await announcementService.dismiss(id);
    const remaining = announcements.filter(a => a.id !== id);
    setAnnouncements(remaining);
    setCurrentIndex(0);
  };

  if (loading || announcements.length === 0) return null;

  const announcement = announcements[currentIndex];
  const c = colors[announcement.type] || colors.info;

  return (
    <div className="px-4 pt-3">
      <div
        role="status"
        aria-live="polite"
        className={`relative overflow-hidden rounded-xl border ${c} shadow-sm max-w-md mx-auto transition-all duration-500`}
      >
        <div className="flex items-center gap-2 pl-3 pr-8 py-2">
          <span className="flex-shrink-0 text-sm">{icons[announcement.type] || '📢'}</span>
          <div className="flex-1 overflow-hidden">
            <div className="animate-marquee whitespace-nowrap text-[11px]">
              <span className="font-semibold">{announcement.title}</span>
              <span className="mx-1.5 opacity-30">·</span>
              <span className="opacity-75">{announcement.message}</span>
              {announcement.cta_text && announcement.cta_url && (
                <a href={announcement.cta_url} className="ml-2 font-semibold underline opacity-90 hover:opacity-100">
                  {announcement.cta_text}
                </a>
              )}
            </div>
          </div>
          {/* Page dots */}
          {announcements.length > 1 && (
            <div className="flex gap-1 flex-shrink-0">
              {announcements.map((_, i) => (
                <div key={i} className={`w-1.5 h-1.5 rounded-full transition-all ${i === currentIndex ? 'bg-current opacity-60' : 'bg-current opacity-20'}`} />
              ))}
            </div>
          )}
          {announcement.dismissible && (
            <button
              onClick={() => handleDismiss(announcement.id)}
              className="absolute top-2 right-2 w-4 h-4 rounded-full bg-black/10 hover:bg-black/20 flex items-center justify-center text-[9px] opacity-50 hover:opacity-80 transition-all"
              aria-label="Dismiss announcement"
            >
              ✕
            </button>
          )}
        </div>
      </div>
      <style>{`
        @keyframes marquee {
          from { transform: translateX(100%); }
          to   { transform: translateX(-100%); }
        }
        .animate-marquee {
          animation: marquee 20s linear infinite;
        }
        @media (prefers-reduced-motion: reduce) {
          .animate-marquee { animation: none; }
        }
      `}</style>
    </div>
  );
}

export default AnnouncementBanner;
