export interface KinSecurityPlugin {
  encrypt(data: string): Promise<{ encrypted: string }>;
  decrypt(encrypted: string): Promise<{ decrypted: string }>;
  storeSecurely(key: string, value: string): Promise<{ success: boolean }>;
  retrieveSecurely(key: string): Promise<{ value: string | null }>;
  deleteSecurely(key: string): Promise<{ success: boolean }>;
  checkBiometrics(): Promise<{ available: boolean; enrolled: boolean }>;
  generateKey(alias: string): Promise<{ success: boolean }>;
}
