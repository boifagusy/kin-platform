import { useVersionCheck } from '../../services/useVersionCheck';
import UpdateDialog from '../dashboard/UpdateDialog';

function VersionGate({ children }) {
  const { showDialog, updateData, policy, loading, error, handleDismiss, handleRetry } = useVersionCheck({ enforceOnStartup: true });

  // Still checking — show nothing or a loader
  if (loading) {
    return (
      <div className="min-h-screen bg-[#F0F7F2] flex items-center justify-center">
        <div className="text-[#1A5632] text-sm">Checking for updates...</div>
      </div>
    );
  }

  // Blocking update — show dialog only, no children
  if (policy === 'required' || policy === 'force') {
    return (
      <div className="min-h-screen bg-[#F0F7F2] flex items-center justify-center">
        <UpdateDialog
          open={true}
          updateData={updateData}
          policy={policy}
          error={error}
          onRetry={handleRetry}
        />
      </div>
    );
  }

  // Optional update or current — show dialog over children
  return (
    <>
      <UpdateDialog
        open={showDialog}
        updateData={updateData}
        policy={policy}
        error={error}
        onDismiss={handleDismiss}
        onRetry={handleRetry}
      />
      {children}
    </>
  );
}

export default VersionGate;
