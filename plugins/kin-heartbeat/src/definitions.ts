export interface KinHeartbeatPlugin {
  echo(options: { value: string }): Promise<{ value: string }>;
}
