import React, { useState, useEffect } from 'react';
import { secureStorage } from '../services/secureStorage';
import sosService from '../services/sosService';

const TestSilentSOS = () => {
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(false);

  const addResult = (name, status, message) => {
    setResults(prev => [...prev, { name, status, message, timestamp: new Date().toLocaleTimeString() }]);
  };

  const runAllTests = async () => {
    setLoading(true);
    setResults([]);

    try {
      // Test 1: Secure Storage
      addResult('Secure Storage', '⏳', 'Running...');
      await secureStorage.set('test_key', 'hello_world');
      const value = await secureStorage.get('test_key');
      if (value === 'hello_world') {
        addResult('Secure Storage', '✅ PASS', 'Stored & retrieved successfully');
      } else {
        addResult('Secure Storage', '❌ FAIL', `Expected 'hello_world', got '${value}'`);
      }

      // Test 2: Duress PIN Storage
      addResult('Duress PIN', '⏳', 'Running...');
      await secureStorage.set('duress_pin', '9999');
      const duress = await secureStorage.get('duress_pin');
      if (duress === '9999') {
        addResult('Duress PIN', '✅ PASS', 'Duress PIN stored successfully');
      } else {
        addResult('Duress PIN', '❌ FAIL', `Expected '9999', got '${duress}'`);
      }

      // Test 3: SOS Service (silent)
      addResult('Silent SOS', '⏳', 'Running...');
      try {
        const result = await sosService.triggerSOS({
          silent: true,
          location: { lat: 6.5244, lng: 3.3792 }
        });
        if (result.success) {
          addResult('Silent SOS', '✅ PASS', 'SOS triggered silently');
        } else {
          addResult('Silent SOS', '⚠️ WARN', result.message || 'SOS triggered with warning');
        }
      } catch (error) {
        addResult('Silent SOS', '❌ FAIL', error.message);
      }

      // Test 4: Silent SOS State
      addResult('Silent State', '⏳', 'Running...');
      const isActive = sosService.isSilentSOSActive();
      addResult('Silent State', isActive ? '✅ ACTIVE' : 'ℹ️ INACTIVE', 
        isActive ? 'Silent SOS is active' : 'No active silent SOS');

    } catch (error) {
      addResult('Test Error', '❌ FAIL', error.message);
    }

    setLoading(false);
  };

  const clearSOS = () => {
    sosService.clearSilentSOS();
    addResult('Clear SOS', '🔄 CLEARED', 'Silent SOS state cleared');
  };

  return (
    <div style={{ padding: '20px', maxWidth: '600px', margin: '0 auto' }}>
      <h1 style={{ fontSize: '24px', marginBottom: '10px' }}>🧪 Silent SOS Test</h1>
      <p style={{ color: '#666', marginBottom: '20px' }}>Tap "Run Tests" to verify Silent SOS functionality</p>

      <div style={{ display: 'flex', gap: '10px', marginBottom: '20px' }}>
        <button
          onClick={runAllTests}
          disabled={loading}
          style={{
            padding: '12px 24px',
            backgroundColor: '#1A5632',
            color: 'white',
            border: 'none',
            borderRadius: '8px',
            fontSize: '16px',
            fontWeight: 'bold',
            cursor: loading ? 'not-allowed' : 'pointer',
            opacity: loading ? 0.6 : 1
          }}
        >
          {loading ? '⏳ Running...' : '▶️ Run Tests'}
        </button>
        <button
          onClick={clearSOS}
          style={{
            padding: '12px 24px',
            backgroundColor: '#dc3545',
            color: 'white',
            border: 'none',
            borderRadius: '8px',
            fontSize: '16px',
            fontWeight: 'bold',
            cursor: 'pointer'
          }}
        >
          🗑️ Clear SOS
        </button>
      </div>

      <div style={{ 
        backgroundColor: '#f8f9fa', 
        borderRadius: '12px', 
        padding: '16px',
        minHeight: '300px'
      }}>
        {results.length === 0 ? (
          <p style={{ color: '#999', textAlign: 'center', padding: '40px 0' }}>
            No tests run yet. Tap "Run Tests" to start.
          </p>
        ) : (
          <div>
            {results.map((result, index) => (
              <div
                key={index}
                style={{
                  display: 'flex',
                  justifyContent: 'space-between',
                  alignItems: 'center',
                  padding: '10px 12px',
                  marginBottom: '6px',
                  backgroundColor: 'white',
                  borderRadius: '8px',
                  borderLeft: `4px solid ${
                    result.status.includes('✅') ? '#22c55e' :
                    result.status.includes('❌') ? '#ef4444' :
                    result.status.includes('⚠️') ? '#f59e0b' :
                    result.status.includes('ℹ️') ? '#3b82f6' :
                    '#94a3b8'
                  }`
                }}
              >
                <div>
                  <strong>{result.name}</strong>
                  <span style={{ marginLeft: '10px', fontSize: '14px' }}>{result.status}</span>
                  <div style={{ fontSize: '12px', color: '#666', marginTop: '2px' }}>{result.message}</div>
                </div>
                <span style={{ fontSize: '11px', color: '#999' }}>{result.timestamp}</span>
              </div>
            ))}
          </div>
        )}
      </div>

      <div style={{ marginTop: '16px', fontSize: '12px', color: '#999' }}>
        💡 Tests run in browser. No actual SOS alerts are sent to contacts.
      </div>
    </div>
  );
};

export default TestSilentSOS;
