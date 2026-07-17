import { useState, useEffect, useRef, useCallback } from 'react';
import announcementService from '../../services/AnnouncementService.js';

function AnnouncementBanner() {
  const [announcement, setAnnouncement] = useState(null);
  const [loading, setLoading] = useState(true);
  const mounted = useRef(true);
  const timer = useRef(null);

  const icons = { info: '📢', warning: '📢', success: '📢', critical: '📢' };
  const colors = {
    info:     'bg-blue-50 border-blue-200 text-blue-700',
    warning:  'bg-amber-50 border-amber-200 text-amber-700',
    success:  'bg-emerald-50 border-emerald-200 text-emerald-700',
    critical: 'bg-red-50 border-red-200 text-red-700',
  };

  const fetchAndShow = useCallback(async () => {
    try {
      const announcements = await announcementService.fetch();
      if (!mounted.current) return;
      if (announcements.length > 0) {
        const sorted = announcements.sort((a, b) => {
          const p = { critical: 4, high: 3, normal: 2, low: 1 };
          return (p[b.priority] || 0) - (p[a.priority] || 0);
        });
        setAnnouncement(sorted[0]);
      }
    } catch (e) {
      // Silent fail — cached data shown by service
    } finally {
      if (mounted.current) setLoading(false);
    }
  }, []);

  useEffect(() => {
    mounted.current = true;
    fetchAndShow();
    return () => { mounted.current = false; };
  }, [fetchAndShow]);

  // Refresh every 5 minutes
  useEffect(() => {
    timer.current = setInterval(fetchAndShow, 300000);
    return () => clearInterval(timer.current);
  }, [fetchAndShow]);

  const handleDismiss = async () => {
    if (!announcement) return;
    try {
      await announcementService.dismiss(announcement.id);
    } catch (e) {
      // Dismiss locally even if persistence fails
    }
    setAnnouncement(null);
  };

  if (loading || !announcement) return null;

  const cta = announcement.cta_text && announcement.cta_url;

  return (
    <div className="px-4 pt-3">
      <div
        role="status"
        aria-live="polite"
        className={`relative overflow-hidden rounded-xl border ${colors[announcement.type] || colors.info} shadow-sm max-w-md mx-auto`}
      >
        <div className="flex items-center gap-2 pl-3 pr-8 py-2">
          <span className="flex-shrink-0 text-sm">{icons[announcement.type] || '📢'}</span>
          <div className="flex-1 overflow-hidden">
            <div className="animate-marquee whitespace-nowrap text-[11px]">
              <span className="font-semibold">{announcement.title}</span>
              <span className="mx-1.5 opacity-30">·</span>
              <span className="opacity-75">{announcement.message}</span>
              {cta && (
                <a href={announcement.cta_url} className="ml-2 font-semibold underline opacity-90 hover:opacity-100">
                  {announcement.cta_text}
                </a>
              )}
            </div>
          </div>
          {announcement.dismissible && (
            <button
              onClick={handleDismiss}
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
