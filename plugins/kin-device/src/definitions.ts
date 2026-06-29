export interface KinDevicePlugin {
  echo(options: { value: string }): Promise<{ value: string }>;
}
