export interface KinNotificationsPlugin {
  echo(options: { value: string }): Promise<{ value: string }>;
}
