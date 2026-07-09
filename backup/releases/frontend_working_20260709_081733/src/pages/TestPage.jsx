import React, { useState } from 'react';

// Import the simple SOS service directly
import sosService from '../services/sosService_simple';

const TestPage = () => {
  const [logs, setLogs] = useState([]);
  const [loading, setLoading] = useState(false);

  const addLog = (msg, type = 'info') => {
    setLogs(prev => [...prev, { msg, type, time: new Date().toLocaleTimeString() }]);
  };

  const runTests = async () => {
    setLoading(true);
    setLogs([]);
    addLog('🧪 Starting Silent SOS tests...', 'info');

    try {
      // Test 1: localStorage
      addLog('📦 Testing localStorage...', 'info');
      try {
        localStorage.setItem('test_key', 'hello_world');
        const value = localStorage.getItem('test_key');
        if (value === 'hello_world') {
          addLog('✅ localStorage: PASSED', 'pass');
        } else {
          addLog('❌ localStorage: FAILED', 'fail');
        }
      } catch (e) {
        addLog(`❌ localStorage error: ${e.message}`, 'fail');
      }

      // Test 2: secureStorage
      addLog('🔐 Testing secureStorage...', 'info');
      try {
        const { secureStorage } = await import('../services/secureStorage');
        await secureStorage.set('test', 'world');
        const val = await secureStorage.get('test');
        if (val === 'world') {
          addLog('✅ secureStorage: PASSED', 'pass');
        } else {
          addLog(`❌ secureStorage: FAILED (got "${val}")`, 'fail');
        }
      } catch (e) {
        addLog(`❌ secureStorage error: ${e.message}`, 'fail');
      }

      // Test 3: Duress PIN
      addLog('🔑 Testing Duress PIN...', 'info');
      try {
        const { secureStorage } = await import('../services/secureStorage');
        await secureStorage.set('duress_pin', '9999');
        const pin = await secureStorage.get('duress_pin');
        if (pin === '9999') {
          addLog('✅ Duress PIN: PASSED', 'pass');
        } else {
          addLog(`❌ Duress PIN: FAILED (got "${pin}")`, 'fail');
        }
      } catch (e) {
        addLog(`❌ Duress PIN error: ${e.message}`, 'fail');
      }

      // Test 4: SOS Service (using the imported service directly)
      addLog('🆘 Testing SOS Service...', 'info');
      try {
        const result = await sosService.triggerSOS({
          silent: true,
          location: { lat: 6.5244, lng: 3.3792 }
        });
        
        if (result && result.success) {
          addLog(`✅ SOS Service: PASSED (${result.message})`, 'pass');
        } else {
          addLog(`⚠️ SOS Service: ${result?.message || 'Unknown'}`, 'warn');
        }
      } catch (e) {
        addLog(`❌ SOS Service error: ${e.message}`, 'fail');
      }

      // Test 5: Check Silent State
      addLog('🔍 Checking Silent SOS state...', 'info');
      try {
        const isActive = sosService.isSilentSOSActive();
        addLog(`📊 Silent SOS Active: ${isActive ? '✅ YES' : '❌ NO'}`, isActive ? 'pass' : 'info');
      } catch (e) {
        addLog(`❌ State check error: ${e.message}`, 'fail');
      }

      // Test 6: Clear Silent SOS
      addLog('🗑️ Testing Clear Silent SOS...', 'info');
      try {
        sosService.clearSilentSOS();
        const isActive = sosService.isSilentSOSActive();
        addLog(`📊 After Clear: Active = ${isActive ? '❌ Still Active' : '✅ Cleared'}`, isActive ? 'fail' : 'pass');
      } catch (e) {
        addLog(`❌ Clear error: ${e.message}`, 'fail');
      }

      addLog('✅ All tests complete!', 'success');

    } catch (e) {
      addLog(`❌ Test error: ${e.message}`, 'fail');
    }

    setLoading(false);
  };

  const clearLogs = () => setLogs([]);

  return (
    <div style={{ padding: '20px', maxWidth: '500px', margin: '0 auto', fontFamily: 'sans-serif' }}>
      <h1 style={{ fontSize: '24px', color: '#1A5632' }}>🧪 Silent SOS Test</h1>
      <p style={{ color: '#666', marginBottom: '20px' }}>Tap "Run Tests" to verify everything</p>
      
      <div style={{ display: 'flex', gap: '10px', marginBottom: '20px' }}>
        <button 
          onClick={runTests}
          disabled={loading}
          style={{
            padding: '12px 24px',
            background: loading ? '#6c757d' : '#1A5632',
            color: 'white',
            border: 'none',
            borderRadius: '8px',
            fontSize: '16px',
            cursor: loading ? 'not-allowed' : 'pointer',
            fontWeight: 'bold',
            flex: 1
          }}
        >
          {loading ? '⏳ Running...' : '▶️ Run Tests'}
        </button>
        <button 
          onClick={clearLogs}
          style={{
            padding: '12px 20px',
            background: '#dc3545',
            color: 'white',
            border: 'none',
            borderRadius: '8px',
            cursor: 'pointer'
          }}
        >
          🗑️ Clear
        </button>
      </div>

      <div style={{
        background: '#1a1a2e',
        color: '#e0e0e0',
        padding: '16px',
        borderRadius: '8px',
        minHeight: '300px',
        maxHeight: '400px',
        overflow: 'auto',
        fontFamily: 'monospace',
        fontSize: '13px',
        lineHeight: '1.8'
      }}>
        {logs.length === 0 ? (
          <div style={{ color: '#666', textAlign: 'center', padding: '40px 0' }}>
            No tests run yet.<br/>Tap "Run Tests" to start.
          </div>
        ) : (
          logs.map((log, i) => (
            <div 
              key={i}
              style={{
                color: log.type === 'pass' ? '#22c55e' :
                       log.type === 'fail' ? '#ef4444' :
                       log.type === 'warn' ? '#f59e0b' :
                       log.type === 'success' ? '#22c55e' :
                       '#e0e0e0',
                marginBottom: '2px',
                wordBreak: 'break-word'
              }}
            >
              [{log.time}] {log.msg}
            </div>
          ))
        )}
      </div>

      <div style={{ marginTop: '12px', fontSize: '12px', color: '#999' }}>
        💡 All tests run in browser. No real alerts are sent.
      </div>
    </div>
  );
};

export default TestPage;
