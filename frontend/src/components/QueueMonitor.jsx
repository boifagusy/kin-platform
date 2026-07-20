import React, { useState, useEffect } from 'react';
import syncCoordinator from '../services/SyncCoordinator.js';
import networkDetection from '../services/NetworkDetection.js';

export const QueueMonitor = () => {
  const [queueSize, setQueueSize] = useState(0);
  const [deadCount, setDeadCount] = useState(0);
  const [status, setStatus] = useState('Ready');
  const [isOnline, setIsOnline] = useState(true);

  const refresh = async () => {
    try {
      const stats = await syncCoordinator.getStatus();
      setQueueSize(stats.queue_size);
      setDeadCount(stats.dead_letter_count);
      const online = await networkDetection.isTrulyOnline();
      setIsOnline(online);
      if (stats.queue_size === 0 && stats.dead_letter_count === 0) {
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
      fontSize: '12px',
      fontFamily: 'monospace',
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'center',
    }}>
      <span>{status}</span>
      <span>Queue: {queueSize} | Dead: {deadCount}</span>
    </div>
  );
};

export default QueueMonitor;
