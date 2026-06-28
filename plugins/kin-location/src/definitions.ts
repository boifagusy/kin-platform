export interface KinLocationPlugin {
  echo(options: { value: string }): Promise<{ value: string }>;
}
