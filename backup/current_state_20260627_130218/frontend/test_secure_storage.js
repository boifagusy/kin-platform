// Test secure storage functionality
const secureStorage = {
  async set(key, value) {
    try {
      const encoded = btoa(encodeURIComponent(JSON.stringify({
        value: value,
        timestamp: Date.now()
      })));
      localStorage.setItem(`kin_secure_${key}`, encoded);
      console.log(`✅ Stored: ${key} = ${value}`);
      return true;
    } catch (error) {
      console.error('❌ Store failed:', error);
      return false;
    }
  },

  async get(key) {
    try {
      const encoded = localStorage.getItem(`kin_secure_${key}`);
      if (!encoded) {
        console.log(`❌ Key not found: ${key}`);
        return null;
      }
      const decoded = JSON.parse(decodeURIComponent(atob(encoded)));
      console.log(`✅ Retrieved: ${key} = ${decoded.value}`);
      return decoded.value;
    } catch (error) {
      console.error('❌ Retrieve failed:', error);
      return null;
    }
  }
};

async function runTest() {
  console.log('\n📦 Testing Secure Storage...\n');
  
  // Test 1: Store a value
  console.log('1️⃣ Storing test value...');
  await secureStorage.set('test_key', 'test_value_123');
  
  // Test 2: Retrieve the value
  console.log('\n2️⃣ Retrieving test value...');
  const value = await secureStorage.get('test_key');
  
  // Test 3: Verify
  console.log('\n3️⃣ Verification:');
  if (value === 'test_value_123') {
    console.log('✅ Secure Storage test PASSED');
  } else {
    console.log(`❌ Secure Storage test FAILED: Expected 'test_value_123', got '${value}'`);
  }
  
  // Test 4: Store duress PIN
  console.log('\n4️⃣ Storing duress PIN...');
  await secureStorage.set('duress_pin', '9999');
  
  // Test 5: Retrieve duress PIN
  console.log('\n5️⃣ Retrieving duress PIN...');
  const duressPin = await secureStorage.get('duress_pin');
  if (duressPin === '9999') {
    console.log('✅ Duress PIN stored and retrieved successfully');
  } else {
    console.log(`❌ Duress PIN test FAILED: Expected '9999', got '${duressPin}'`);
  }
  
  console.log('\n✅ All secure storage tests complete!');
}

runTest();
