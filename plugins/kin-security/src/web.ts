import { WebPlugin } from '@capacitor/core';
import type { KinSecurityPlugin } from './definitions';

export class KinSecurityWeb extends WebPlugin implements KinSecurityPlugin {
  private encoder = new TextEncoder();
  private decoder = new TextDecoder();
  private encryptionKey: CryptoKey | null = null;
  private initialized = false;

  private async initialize(): Promise<void> {
    if (this.initialized) return;
    this.encryptionKey = await this.getOrCreateKey();
    this.initialized = true;
  }

  private async getOrCreateKey(): Promise<CryptoKey> {
    const stored = await this.getStoredKey();
    if (stored) return stored;
    
    const key = await crypto.subtle.generateKey(
      {
        name: 'AES-GCM',
        length: 256,
      },
      true,
      ['encrypt', 'decrypt']
    );
    
    await this.storeKey(key);
    return key;
  }

  private async storeKey(key: CryptoKey): Promise<void> {
    const exported = await crypto.subtle.exportKey('raw', key);
    const bytes = new Uint8Array(exported);
    const base64 = btoa(String.fromCharCode(...bytes));
    localStorage.setItem('kin_encryption_key', base64);
  }

  private async getStoredKey(): Promise<CryptoKey | null> {
    const stored = localStorage.getItem('kin_encryption_key');
    if (!stored) return null;
    
    const bytes = Uint8Array.from(atob(stored), c => c.charCodeAt(0));
    return await crypto.subtle.importKey(
      'raw',
      bytes,
      'AES-GCM',
      true,
      ['encrypt', 'decrypt']
    );
  }

  async encrypt(data: string): Promise<{ encrypted: string }> {
    try {
      await this.initialize();
      if (!data) throw new Error('Data is required');
      
      const iv = crypto.getRandomValues(new Uint8Array(12));
      const encoded = this.encoder.encode(data);
      
      const encrypted = await crypto.subtle.encrypt(
        {
          name: 'AES-GCM',
          iv: iv,
        },
        this.encryptionKey!,
        encoded
      );
      
      const combined = new Uint8Array(iv.length + encrypted.byteLength);
      combined.set(iv);
      combined.set(new Uint8Array(encrypted), iv.length);
      
      return { encrypted: btoa(String.fromCharCode(...combined)) };
    } catch (error) {
      console.error('Encryption failed:', error);
      throw new Error(`Encryption failed: ${error instanceof Error ? error.message : 'Unknown error'}`);
    }
  }

  async decrypt(encrypted: string): Promise<{ decrypted: string }> {
    try {
      await this.initialize();
      if (!encrypted) throw new Error('Encrypted data is required');
      
      const combined = Uint8Array.from(atob(encrypted), c => c.charCodeAt(0));
      const iv = combined.slice(0, 12);
      const data = combined.slice(12);
      
      const decrypted = await crypto.subtle.decrypt(
        {
          name: 'AES-GCM',
          iv: iv,
        },
        this.encryptionKey!,
        data
      );
      
      return { decrypted: this.decoder.decode(decrypted) };
    } catch (error) {
      console.error('Decryption failed:', error);
      throw new Error(`Decryption failed: ${error instanceof Error ? error.message : 'Unknown error'}`);
    }
  }

  async storeSecurely(key: string, value: string): Promise<{ success: boolean }> {
    try {
      const encrypted = await this.encrypt(value);
      localStorage.setItem(`kin_secure_${key}`, encrypted.encrypted);
      return { success: true };
    } catch (error) {
      console.error('Store failed:', error);
      return { success: false };
    }
  }

  async retrieveSecurely(key: string): Promise<{ value: string | null }> {
    try {
      const stored = localStorage.getItem(`kin_secure_${key}`);
      if (!stored) return { value: null };
      const decrypted = await this.decrypt(stored);
      return { value: decrypted.decrypted };
    } catch (error) {
      console.error('Retrieve failed:', error);
      return { value: null };
    }
  }

  async deleteSecurely(key: string): Promise<{ success: boolean }> {
    try {
      localStorage.removeItem(`kin_secure_${key}`);
      return { success: true };
    } catch {
      return { success: false };
    }
  }

  async checkBiometrics(): Promise<{ available: boolean; enrolled: boolean }> {
    return { available: false, enrolled: false };
  }

  async generateKey(alias: string): Promise<{ success: boolean }> {
    return { success: true };
  }
}
