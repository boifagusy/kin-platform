export interface KinNetworkPlugin {
  echo(options: { value: string }): Promise<{ value: string }>;
}
