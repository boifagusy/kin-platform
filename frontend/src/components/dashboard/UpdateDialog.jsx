import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';

function UpdateDialog({ open, updateData, onUpdate, onDismiss }) {
  if (!open || !updateData) return null;

  const { latest_version_name, release_notes, force_update, channels } = updateData;
  const updateUrl = channels?.[0]?.url || '#';

  return (
    <div className="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-5">
      <div className="bg-white rounded-2xl w-full max-w-sm shadow-xl overflow-hidden">
        <div className="bg-[#1A5632] px-5 py-4">
          <h2 className="text-white font-bold text-lg">Update Available</h2>
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
              onClick={onUpdate || (() => window.open(updateUrl, '_blank'))}
              className="flex-1 py-3 rounded-xl bg-[#1A5632] text-white font-semibold text-sm active:scale-95 transition-all"
            >
              Update Now
            </button>
            {!force_update && (
              <button
                onClick={onDismiss}
                className="flex-1 py-3 rounded-xl bg-gray-100 text-gray-600 font-semibold text-sm active:scale-95 transition-all"
              >
                Later
              </button>
            )}
          </div>

          {force_update && (
            <p className="text-xs text-red-500 text-center mt-3">This update is required to continue using KIN.</p>
          )}
        </div>
      </div>
    </div>
  );
}

export default UpdateDialog;
