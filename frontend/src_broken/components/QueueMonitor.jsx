import React, { useState, useEffect } from 'react';
import { LocationQueue } from '../services/LocationQueue.js';
import networkDetection from '../services/NetworkDetection.js';

export const QueueMonitor = () => {
  const [queueSize, setQueueSize] = useState(0);
  const [status, setStatus] = useState('Ready');
  const [isOnline, setIsOnline] = useState(true);

  const queue = new LocationQueue();

  const refresh = async () => {
    try {
      const size = await queue.size();
      setQueueSize(size);
      const online = await networkDetection.isTrulyOnline();
      setIsOnline(online);
      if (size === 0) {
        setStatus('✅ All synced');
      } else if (online) {
        setStatus('📤 Syncing...');
      } else {
        setStatus('📴 Offline — waiting for connection');
      }
    } catch (error) {
      setStatus('❌ Error');
    }
  };

  useEffect(() => {
    refresh();
    const interval = setInterval(refresh, 5000);
    return () => clearInterval(interval);
  }, []);

  return (
    <div style={{
      background: '#1a1a2e',
      color: '#fff',
      padding: '8px 16px',
      borderRadius: '8px',
      margin: '8px 0',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'space-between',
      fontSize: '12px',
      flexWrap: 'wrap',
      gap: '8px'
    }}>
      <span>📊 Queue: <strong>{queueSize}</strong></span>
      <span>{isOnline ? '📶 Online' : '📴 Offline'}</span>
      <span style={{ color: queueSize === 0 ? '#4CAF50' : '#FF9800' }}>
        {status}
      </span>
      <button
        onClick={refresh}
        style={{
          background: '#2196F3',
          color: '#fff',
          border: 'none',
          padding: '2px 12px',
          borderRadius: '4px',
          fontSize: '11px',
          cursor: 'pointer'
        }}
      >
        🔄
      </button>
    </div>
  );
};
