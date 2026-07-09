import React from 'react';
import DisguisedCheckinButton from '../components/DisguisedCheckinButton';

const TestDisguisedCheckin = () => {
  const handleNormalCheckIn = () => {
    console.log('📋 Normal check-in performed');
  };

  return (
    <div className="min-h-screen bg-gray-100 flex items-center justify-center p-4">
      <div className="bg-white rounded-2xl p-8 shadow-lg max-w-md w-full">
        <h1 className="text-2xl font-bold text-[#1A5632] text-center mb-1">
          🕵️ Disguised Check-in Test
        </h1>
        <p className="text-gray-500 text-sm text-center mb-8">
          Tap the button 5 times quickly to trigger disguised SOS
        </p>

        <div className="flex justify-center mb-6">
          <DisguisedCheckinButton onCheckIn={handleNormalCheckIn} />
        </div>

        <div className="bg-gray-50 rounded-lg p-4 text-sm text-gray-600">
          <strong className="font-semibold">💡 How it works:</strong>
          <ul className="list-disc pl-5 mt-2 space-y-1">
            <li>Tap the button 5 times within 3 seconds</li>
            <li>This triggers Silent SOS (no visible alert)</li>
            <li>Appears as a normal check-in</li>
            <li>5-minute cooldown after triggering</li>
          </ul>
        </div>
      </div>
    </div>
  );
};

export default TestDisguisedCheckin;
