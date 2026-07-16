import { useState, useEffect } from 'react';
import offlineWrite from '../../services/OfflineWriteService.js';

function SyncStatus() {
  const [pending, setPending] = useState(0);
  const [online, setOnline] = useState(navigator.onLine);

  useEffect(() => {
    const check = async () => {
      const count = await offlineWrite.pendingCount();
      setPending(count);
    };
    check();
    const interval = setInterval(check, 5000);

    const handleOnline = () => { setOnline(true); check(); };
    const handleOffline = () => setOnline(false);
    window.addEventListener('online', handleOnline);
    window.addEventListener('offline', handleOffline);

    return () => {
      clearInterval(interval);
      window.removeEventListener('online', handleOnline);
      window.removeEventListener('offline', handleOffline);
    };
  }, []);

  if (!online && pending === 0) {
    return (
      <div className="bg-yellow-100 p-2 text-center">
        <p className="text-xs font-medium text-yellow-800">📴 Offline — changes will sync when connected</p>
      </div>
    );
  }

  if (pending > 0) {
    return (
      <div className="bg-blue-100 p-2 text-center">
        <p className="text-xs font-medium text-blue-800">⏳ {pending} pending change{pending > 1 ? 's' : ''} — syncing...</p>
      </div>
    );
  }

  return null;
}

export default SyncStatus;
