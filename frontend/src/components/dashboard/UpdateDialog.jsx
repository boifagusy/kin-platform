import { useState } from 'react';

function UpdateDialog({ open, updateData, policy, onDismiss, onRetry, error }) {
  if (!open) return null;

  // Error state
  if (error) {
    return (
      <div className="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-5">
        <div className="bg-white rounded-2xl w-full max-w-sm shadow-xl p-6 text-center">
          <p className="text-red-500 text-sm mb-4">{error}</p>
          <button
            onClick={onRetry}
            className="w-full py-3 rounded-xl bg-[#1A5632] text-white font-semibold text-sm"
          >
            Retry
          </button>
        </div>
      </div>
    );
  }

  if (!updateData) return null;

  const { latest_version_name, release_notes, channels } = updateData;
  const channel = channels?.[0];
  const updateUrl = channel?.url || '#';
  const isBlocking = policy === 'required' || policy === 'force';

  return (
    <div className="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-5">
      <div className="bg-white rounded-2xl w-full max-w-sm shadow-xl overflow-hidden">
        <div className={`px-5 py-4 ${policy === 'force' ? 'bg-red-600' : 'bg-[#1A5632]'}`}>
          <h2 className="text-white font-bold text-lg">
            {policy === 'force' ? 'Critical Update Required' : 'Update Available'}
          </h2>
          <p className="text-green-200 text-sm mt-0.5">Version {latest_version_name}</p>
        </div>

        <div className="p-5">
          {release_notes && (
            <div className="mb-4">
              <p className="text-xs font-semibold text-gray-500 uppercase mb-1">What's New</p>
              <p className="text-sm text-gray-700 leading-relaxed">{release_notes}</p>
            </div>
          )}

          <div className="flex gap-3">
            <button
              onClick={() => window.open(updateUrl, '_blank')}
              className="flex-1 py-3 rounded-xl bg-[#1A5632] text-white font-semibold text-sm active:scale-95 transition-all"
            >
              Update Now
            </button>
            {!isBlocking && (
              <button
                onClick={onDismiss}
                className="flex-1 py-3 rounded-xl bg-gray-100 text-gray-600 font-semibold text-sm active:scale-95 transition-all"
              >
                Later
              </button>
            )}
          </div>

          {isBlocking && (
            <p className="text-xs text-red-500 text-center mt-3">
              {policy === 'force'
                ? 'A critical update is required. You must update to continue using KIN.'
                : 'This update is required to continue using KIN.'}
            </p>
          )}
        </div>
      </div>
    </div>
  );
}

export default UpdateDialog;
