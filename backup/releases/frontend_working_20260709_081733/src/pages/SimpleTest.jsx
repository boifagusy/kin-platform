import React from 'react';

const SimpleTest = () => {
  return (
    <div style={{ padding: '40px', textAlign: 'center' }}>
      <h1>✅ React is Working!</h1>
      <p>If you can see this, React is rendering correctly.</p>
      <button 
        onClick={() => alert('Button clicked!')}
        style={{
          padding: '12px 24px',
          background: '#1A5632',
          color: 'white',
          border: 'none',
          borderRadius: '8px',
          cursor: 'pointer'
        }}
      >
        Click Me
      </button>
    </div>
  );
};

export default SimpleTest;
