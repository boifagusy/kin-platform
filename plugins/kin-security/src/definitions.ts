export interface KinSecurityPlugin {
  echo(options: { value: string }): Promise<{ value: string }>;
}
