// This script clears IndexedDB directly
// Run in browser console or via Node.js with browser polyfill

const DB_NAME = 'KinLocationDB';
const STORE_NAME = 'locationQueue';

async function clearIndexedDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open(DB_NAME, 1);
    request.onsuccess = (event) => {
      const db = event.target.result;
      const tx = db.transaction([STORE_NAME], 'readwrite');
      const store = tx.objectStore(STORE_NAME);
      const clear = store.clear();
      clear.onsuccess = () => {
        console.log('✅ IndexedDB cleared');
        resolve();
      };
      clear.onerror = () => {
        console.error('❌ Failed to clear IndexedDB');
        reject();
      };
    };
    request.onerror = () => {
      console.error('❌ Failed to open database');
      reject();
    };
  });
}

clearIndexedDB();
