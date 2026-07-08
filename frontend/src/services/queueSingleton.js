import { LocationQueue } from './LocationQueue.js';

// Singleton instance — persists across page refreshes
let queueInstance = null;

export function getQueue() {
  if (!queueInstance) {
    queueInstance = new LocationQueue();
  }
  return queueInstance;
}

export const queue = getQueue();
