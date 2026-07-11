import { describe, it, expect } from 'vitest';

describe('Test Setup', () => {
  it('should have working tests', () => {
    expect(true).toBe(true);
  });

  it('should have localStorage mocked', () => {
    const key = 'test_key';
    const value = 'test_value';
    
    localStorage.setItem(key, value);
    expect(localStorage.setItem).toHaveBeenCalledWith(key, value);
    
    localStorage.getItem(key);
    expect(localStorage.getItem).toHaveBeenCalledWith(key);
  });
});
