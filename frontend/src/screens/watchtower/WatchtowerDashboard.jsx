import React from 'react'

const WatchtowerDashboard = () => {
  return (
    <div style={{ padding: '24px', backgroundColor: '#f5f5f5', minHeight: '100vh' }}>
      <h1 style={{ fontSize: '24px', fontWeight: 'bold', color: '#171717' }}>
        🔍 Watchtower Dashboard
      </h1>
      <p style={{ color: '#737373', marginTop: '8px' }}>
        System monitoring and operations dashboard
      </p>
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: '16px', marginTop: '24px' }}>
        {[1, 2, 3, 4].map((i) => (
          <div key={i} style={{ 
            backgroundColor: 'white', 
            padding: '16px', 
            borderRadius: '8px', 
            boxShadow: '0 1px 3px rgba(0,0,0,0.1)',
            border: '1px solid #e8e8e8'
          }}>
            <div style={{ height: '16px', backgroundColor: '#e8e8e8', width: '50%', marginBottom: '8px', borderRadius: '4px' }}></div>
            <div style={{ height: '32px', backgroundColor: '#d4d4d4', width: '75%', borderRadius: '4px' }}></div>
          </div>
        ))}
      </div>
    </div>
  )
}

export default WatchtowerDashboard
