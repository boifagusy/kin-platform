export interface KinCorePlugin {
  echo(options: { value: string }): Promise<{ value: string }>;
}
