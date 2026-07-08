import React, { useState, useEffect } from 'react';
import { LocationQueue } from '../services/LocationQueue.js';

export const PersistenceTest = () => {
  const [size, setSize] = useState(0);
  const queue = new LocationQueue();

  const refresh = async () => {
    const s = await queue.size();
    setSize(s);
  };

  const enqueue = async () => {
    await queue.enqueue({ test: Date.now() });
    await refresh();
  };

  const clear = async () => {
    await queue.clear();
    await refresh();
  };

  useEffect(() => {
    refresh();
  }, []);

  return (
    <div style={{
      background: '#1a1a2e',
      color: '#fff',
      padding: '10px',
      borderRadius: '8px',
      margin: '10px 0',
      display: 'flex',
      gap: '10px',
      alignItems: 'center',
      fontSize: '14px',
      flexWrap: 'wrap'
    }}>
      <span>📊 Queue: <strong>{size}</strong></span>
      <button onClick={enqueue} style={{ background: '#4CAF50', color: '#fff', border: 'none', padding: '4px 12px', borderRadius: '4px', cursor: 'pointer' }}>
        Enqueue
      </button>
      <button onClick={clear} style={{ background: '#f44336', color: '#fff', border: 'none', padding: '4px 12px', borderRadius: '4px', cursor: 'pointer' }}>
        Clear
      </button>
      <button onClick={refresh} style={{ background: '#2196F3', color: '#fff', border: 'none', padding: '4px 12px', borderRadius: '4px', cursor: 'pointer' }}>
        Refresh
      </button>
    </div>
  );
};
