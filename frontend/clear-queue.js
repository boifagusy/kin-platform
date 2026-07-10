import { LocationQueue } from './src/services/LocationQueue.js';

async function clearQueue() {
  const queue = new LocationQueue();
  const size = await queue.size();
  console.log(`📊 Queue size before clear: ${size}`);
  
  await queue.clear();
  console.log('✅ Queue cleared');
  
  const newSize = await queue.size();
  console.log(`📊 Queue size after clear: ${newSize}`);
}

clearQueue().catch(console.error);
