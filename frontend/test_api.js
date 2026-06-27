// Test API integration
async function testAPI() {
  console.log('\n📡 Testing API Integration...\n');
  
  // Test 1: Health check
  console.log('1️⃣ Checking health endpoint...');
  try {
    const response = await fetch('http://localhost:8000/api/v1/health');
    const data = await response.json();
    console.log('✅ Health check passed:', data);
  } catch (error) {
    console.log('⚠️ Health check failed (backend may not be running):', error.message);
  }
  
  // Test 2: SOS endpoint
  console.log('\n2️⃣ Testing SOS endpoint...');
  try {
    const response = await fetch('http://localhost:8000/api/v1/sos', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Silent': 'true',
      },
      body: JSON.stringify({
        location: { lat: 6.5244, lng: 3.3792 },
        is_duress: false,
        silent: true,
        timestamp: new Date().toISOString(),
      }),
    });
    console.log('✅ SOS endpoint responded with status:', response.status);
  } catch (error) {
    console.log('⚠️ SOS endpoint test failed:', error.message);
  }
  
  console.log('\n✅ API tests complete!');
}

testAPI();
