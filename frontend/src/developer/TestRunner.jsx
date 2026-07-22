import { useState } from 'react';
import { useVerification } from './contexts/VerificationContext';
import { E1_TESTS } from './data/e1Tests';

export default function TestRunner() {
  const { context, login, createScenario, setResponse, addResult, clearResults } = useVerification();
  const [running, setRunning] = useState(false);

  const runTest = async (test) => {
    const startTime = performance.now();

    // Login as the required role
    const loggedIn = await login(test.role);
    if (!loggedIn) {
      addResult({ ...test, actual: { status: 0, message: 'Login failed' }, duration: 0, passed: false });
      return;
    }

    // Create scenario (fresh SOS for each test)
    const incidentId = await createScenario('active');
    if (!incidentId) {
      addResult({ ...test, actual: { status: 0, message: 'Scenario creation failed' }, duration: 0, passed: false });
      return;
    }

    // Substitute placeholders
    const endpoint = test.endpoint.replace('{incidentId}', incidentId);
    const token = context.authToken || localStorage.getItem('kin_token');

    try {
      const res = await fetch(`http://localhost:8000/api/v1${endpoint}`, {
        method: test.method,
        headers: { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` },
        body: test.body ? JSON.stringify(test.body) : undefined,
      });
      const data = await res.json().catch(() => ({}));
      const duration = Math.round(performance.now() - startTime);

      setResponse({ endpoint, method: test.method, status: res.status, data, duration });

      // Assert
      let passed = res.status === test.expected.status;
      if (test.expected.message) passed = passed && data.message === test.expected.message;
      if (test.expected.data) {
        for (const [key, value] of Object.entries(test.expected.data)) {
          if (data.data?.[key] !== value) passed = false;
        }
        if (test.expected.data.success !== undefined) passed = passed && data.success === test.expected.data.success;
      }

      addResult({ ...test, actual: { status: res.status, data }, duration, passed });
    } catch (err) {
      addResult({ ...test, actual: { status: 0, message: err.message }, duration: Math.round(performance.now() - startTime), passed: false });
    }
  };

  const runSuite = async (suite) => {
    setRunning(true);
    clearResults();
    for (const test of suite) {
      await runTest(test);
    }
    setRunning(false);
  };

  return (
    <div className="space-y-4">
      <div className="flex gap-2">
        <button onClick={() => runSuite(E1_TESTS)} disabled={running}
          className="px-4 py-2 bg-[#1A5632] text-white rounded-lg text-sm disabled:opacity-50">
          {running ? 'Running...' : '▶ Run E1 Tests'}
        </button>
      </div>

      {context.results.map((r, i) => (
        <div key={i} className={`p-3 rounded-lg text-sm ${r.passed ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'}`}>
          <div className="flex items-center gap-2">
            <span>{r.passed ? '✅' : '❌'}</span>
            <span className="font-medium">{r.id}: {r.name}</span>
          </div>
          <div className="text-xs text-gray-500 mt-1">
            Expected: {r.expected.status} → Got: {r.actual.status} | {r.duration}ms
            {!r.passed && <p className="text-red-600 mt-1">{JSON.stringify(r.actual)}</p>}
          </div>
        </div>
      ))}
    </div>
  );
}
