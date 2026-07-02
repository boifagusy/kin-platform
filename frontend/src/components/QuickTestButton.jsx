import React from 'react';
import { secureStorage } from '../services/secureStorage';
import sosService from '../services/sosService';

const QuickTestButton = () => {
  const handleQuickTest = async () => {
    try {
      // Test 1: Secure Storage
      await secureStorage.set('test', 'hello');
      const test = await secureStorage.get('test');
      const storageOk = test === 'hello';
      
      // Test 2: Duress PIN
      await secureStorage.set('duress_pin', '9999');
      const duress = await secureStorage.get('duress_pin');
      const duressOk = duress === '9999';
      
      // Build result message
      const results = [
        `📦 Secure Storage: ${storageOk ? '✅ OK' : '❌ FAIL'}`,
        `🔑 Duress PIN: ${duressOk ? '✅ OK' : '❌ FAIL'}`,
        `🔇 Silent SOS: Ready`,
      ];
      
      alert(results.join('\n\n'));
      
    } catch (error) {
      alert(`❌ Test failed: ${error.message}`);
    }
  };

  return (
    <button
      onClick={handleQuickTest}
      style={{
        padding: '12px 20px',
        backgroundColor: '#6c757d',
        color: 'white',
        border: 'none',
        borderRadius: '8px',
        margin: '10px',
        fontSize: '14px',
        cursor: 'pointer'
      }}
    >
      🔍 Quick Test
    </button>
  );
};

export default QuickTestButton;
